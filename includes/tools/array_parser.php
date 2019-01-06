<?php

// TODO: Need to phpbb-ise this file, split the classes out into several files

// Modified from: http://jsteemann.github.io/blog/2015/06/16/parsing-php-arrays-with-php/
// class to manage tokens
class Tokens {
	private $tokens;

	public function __construct ($code) {
		// construct PHP code from string and tokenize it
		$tokens = token_get_all("<?php " . $code);
		// kick out whitespace tokens
		$this->tokens = array_filter($tokens, function ($token) {
			return (! is_array($token) || $token[0] !== T_WHITESPACE);
		});
		// remove start token (<?php)
		$this->pop();
	}

	public function done () {
		return count($this->tokens) === 0;
	}

	public function pop () {
		// consume the token and return it
		if ($this->done()) {
			throw new Exception("already at end of tokens!");
		}
		return array_shift($this->tokens);
	}

	public function peek () {
		// return next token, don't consume it
		if ($this->done()) {
			throw new Exception("already at end of tokens!");
		}
		return $this->tokens[0];
	}

	public function doesMatch ($what) {
		$token = $this->peek();

		if (is_string($what) && ! is_array($token) && $token === $what) {
			return true;
		}
		if (is_int($what) && is_array($token) && $token[0] === $what) {
			return true;
		}
		return false;
	}

	public function forceMatch ($what) {
		if (! $this->doesMatch($what)) {
			if (is_int($what)) {
				throw new Exception("unexpected token - expecting " . token_name($what));
			}
			throw new Exception("unexpected token - expecting " . $what);
		}
		// consume the token
		$this->pop();
	}
}

// parser for simple PHP arrays
class Parser {
	private static $CONSTANTS = array(
		"null" => null,
		"true" => true,
		"false" => false
	);

	private $tokens;

	public function __construct(Tokens $tokens) {
		$this->tokens = $tokens;
	}

	public function parseValue () {
		// Ignore values that rely on another variable
		if (!$this->tokens->done()) {
			if (count($this->tokens->peek()) > 1 && substr($this->tokens->peek()[1], 0, 1) === "$") {
				while (!$this->tokens->doesMatch(",")) {
					$this->tokens->pop(); // ignore comments
				}

				return "-- Not a string literal. --"; // is there a better way to handle values that have variables?
			}
		}

		if ($this->tokens->doesMatch(T_CONSTANT_ENCAPSED_STRING)) {
			// strings
			$token = $this->tokens->pop();
			return stripslashes(substr($token[1], 1, -1));
		}

		if ($this->tokens->doesMatch(T_STRING)) {
			// built-in string literals: null, false, true
			$token = $this->tokens->pop();
			$value = strtolower($token[1]);
			if (array_key_exists($value, self::$CONSTANTS)) {
				return self::$CONSTANTS[$value];
			}
			throw new Exception("unexpected string literal " . $token[1]);
		}

		else if ($this->tokens->doesMatch(T_ARRAY) || $this->tokens->doesMatch('[')) {
			$squareBracketArray = $this->tokens->doesMatch('[');

			return $this->parseArray($squareBracketArray);
		}

		// the rest...
		// we expect a number here
		$uminus = 1;

		if ($this->tokens->doesMatch("-")) {
			// unary minus
			$this->tokens->forceMatch("-");
			$uminus = -1;
		}

		if ($this->tokens->doesMatch(T_LNUMBER)) {
			// long number
			$value = $this->tokens->pop();
			return $uminus * (int) $value[1];
		}
		if ($this->tokens->doesMatch(T_DNUMBER)) {
			// double number
			$value = $this->tokens->pop();
			return $uminus * (double) $value[1];
		}

		throw new Exception("unexpected value token");
	}

	public function ignoreComments() {
		if (!$this->tokens->done()) {
			while (count($this->tokens->peek()) > 1 && substr($this->tokens->peek()[1], 0, 2) === "//") {
				$this->tokens->pop(); // ignore comments
			}
		}
	}

	public function parseArray ($square = false) {
		$found = 0;
		$result = array();

		if ($square) {
			$this->tokens->forceMatch("[");
		}

		else {
			$this->tokens->forceMatch(T_ARRAY);
			$this->tokens->forceMatch("(");
		}

		while (true) {
			if ($this->tokens->doesMatch(")") || $this->tokens->doesMatch("]")) {
				// reached the end of the array
				if ($square) {
					$this->tokens->forceMatch("]");
				}

				else {
					$this->tokens->forceMatch(")");
				}

				if ($this->tokens->doesMatch(",")) {
					$this->tokens->forceMatch(",");
				}

				if ($this->tokens->doesMatch(";")) {
					$this->tokens->forceMatch(";");
				}

				break;
			}

			if ($found > 0) {
				// we must see a comma following the first element
				$this->tokens->forceMatch(",");
				$this->ignoreComments();

				if ($square && $this->tokens->doesMatch("]")) {
					// end of the square bracket array
					$this->tokens->forceMatch("]");
					break;
				}

				if ($this->tokens->doesMatch(")")) {
					// reached the end of the array
					$this->tokens->forceMatch(")");
					break;
				}

			}

			$this->ignoreComments();

			if ($this->tokens->doesMatch(T_ARRAY) || $this->tokens->doesMatch('[')) {
				$squareBracketArray = $this->tokens->doesMatch('[');
				// nested array
				$result[] = $this->parseArray($squareBracketArray);
			}
			else if ($this->tokens->doesMatch(T_CONSTANT_ENCAPSED_STRING) || $this->tokens->doesMatch(T_LNUMBER)) {
				// string
				$string = $this->parseValue();

				if ($this->tokens->doesMatch(T_DOUBLE_ARROW)) {
					// array key (key => value)
					$this->tokens->pop();
					$result[$string] = $this->parseValue();
				}
				else {
					// simple string
					$result[] = $string;
				}
			}
			else {
				$result[] = $this->parseValue();
			}

			++$found;
		}
		return $result;
	}
}

/*
$langFiles = array(
	'language/' . LANG_ISO . '/acp/attachments.php',
	'language/' . LANG_ISO . '/acp/ban.php',
	'language/' . LANG_ISO . '/acp/board.php',
	'language/' . LANG_ISO . '/acp/bots.php',
	'language/' . LANG_ISO . '/acp/common.php',
	'language/' . LANG_ISO . '/acp/database.php',
	'language/' . LANG_ISO . '/acp/email.php',
	'language/' . LANG_ISO . '/acp/extensions.php',
	'language/' . LANG_ISO . '/acp/forums.php',
	'language/' . LANG_ISO . '/acp/groups.php',
	'language/' . LANG_ISO . '/acp/language.php',
	'language/' . LANG_ISO . '/acp/modules.php',
	'language/' . LANG_ISO . '/acp/permissions.php',
	'language/' . LANG_ISO . '/acp/permissions_phpbb.php',
	'language/' . LANG_ISO . '/acp/posting.php',
	'language/' . LANG_ISO . '/acp/profile.php',
	'language/' . LANG_ISO . '/acp/prune.php',
	'language/' . LANG_ISO . '/acp/search.php',
	'language/' . LANG_ISO . '/acp/styles.php',
	'language/' . LANG_ISO . '/acp/users.php',
	'language/' . LANG_ISO . '/app.php',
	'language/' . LANG_ISO . '/captcha_qa.php',
	'language/' . LANG_ISO . '/captcha_recaptcha.php',
	'language/' . LANG_ISO . '/cli.php',
	'language/' . LANG_ISO . '/common.php',
	'language/' . LANG_ISO . '/groups.php',
	'language/' . LANG_ISO . '/help/bbcode.php',
	'language/' . LANG_ISO . '/help/faq.php',
	'language/' . LANG_ISO . '/install.php',
	'language/' . LANG_ISO . '/mcp.php',
	'language/' . LANG_ISO . '/memberlist.php',
	'language/' . LANG_ISO . '/migrator.php',
	'language/' . LANG_ISO . '/plupload.php',
	'language/' . LANG_ISO . '/posting.php',
	'language/' . LANG_ISO . '/search.php',
	'language/' . LANG_ISO . '/ucp.php',
	'language/' . LANG_ISO . '/viewforum.php',
	'language/' . LANG_ISO . '/viewtopic.php',
);
echo 'test';
*/

/*
ob_start();
include(LANG_PATH . 'language/' . LANG_ISO . '/common.php');
$common_lang = ob_get_clean();
var_dump($common_lang);
*/



/*

// Parse every lang file
foreach ($langFiles as $langFile) {
	echo '<h1>' . $langFile . '</h1>';

	$filename = LANG_PATH . $langFile;
	$file = file_get_contents($filename);
	preg_match_all('/\$lang\s+=\s+array_merge\(\$lang, array\((.*?)\)\);/s', $file, $matches);

	foreach ($matches[1] as $match) {
		$string = 'array(' . $match . ')';
		$tokens = new Tokens($string);
		$parser = new Parser($tokens);
		$result = $parser->parseArray();

		// check if the parser matched the whole string or if there's something left at the end
		if (!$tokens->done()) {
			throw new Exception("still tokens left after parsing");
		}

		var_dump($result);
	}
}

*/

class array_parser
{
	/**
	 * Find the array of subscribed events
	 * @param $file_path
	 * @return array
	 * @throws Exception
	 */
	public static function check_events($file_path)
	{
		$result = [];

		// Open up the file and get the text
		$file = file_get_contents($file_path);

		// Check to see if it extends EventSubscriberInterface and then get the list of subscribed events
		preg_match_all('/\bclass\s+\S+\s+implements\s+EventSubscriberInterface\s*{\s*(?:[^{}]*{[^{}]*})*[^{}]*\bgetSubscribedEvents\(\)\s*{[^{}]*?(?:\barray\s*\(|\[)([^{}]*?)(?:\)|\])[^{}]*}/x', $file, $matches);

		// Only continue if there's a match
		if (count($matches[1]))
		{
			$match = $matches[1][0];

			$string = 'array(' . $match . ')';
			$tokens = new Tokens($string);
			$parser = new Parser($tokens);
			$result = $parser->parseArray();

			// check if the parser matched the whole string or if there's something left at the end
			if (!$tokens->done())
			{
				throw new Exception("still tokens left after parsing");
			}
		}

		return $result;
	}
}