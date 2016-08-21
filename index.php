<?php

define('CRITICAL_PAGE_EXCERPT_LENGTH', 1000);

while (true) {
	// Get content of random article from Wikipedia in JSON format
	$wiki_json_str = file_get_contents('https://en.wikipedia.org/w/api.php?action=query&generator=random&grnnamespace=0&prop=extracts&exintro=&format=json');
	$wiki_json = json_decode($wiki_json_str, true);

	// Extract the important parts
	$pages = $wiki_json['query']['pages'];
	$page_id;
	$page_extract;
	foreach ($pages as $page) {
		$page_id = $page['pageid'];
		$page_extract = $page['extract'];
		break;
	}

	// Remove all br line breaks
	$page_extract = str_replace('<br>', '', $page_extract);

	// Fetch the next random article if the current matches any of the following criteria 
	if (!preg_match('/^<p><b>/', $page_extract) ||
		preg_match('/can refer to/', $page_extract) ||
		preg_match('/may be/', $page_extract) ||
		preg_match('/may mean/', $page_extract) ||
		preg_match('/may refer to/', $page_extract) ||
		preg_match('/may also refer to/', $page_extract) ||
		preg_match('/list of/', $page_extract)) {
		continue;
	}

	// Remove everything from $page_extract after "<ol" which is a list of citations that shouldn't be shown
	if (preg_match('/<ol/', $page_extract)) {
		$page_extract = substr($page_extract, 0, strpos($page_extract, '<ol'));
	}

	// Cut $page_extract if it is longer than the given critical length
	if (strlen($page_extract) > CRITICAL_PAGE_EXCERPT_LENGTH) {
		// Get the position of the last p closing tag
		$index_of_last_valid_paragraph_closing_tag = strpos(substr($page_extract, 0, CRITICAL_PAGE_EXCERPT_LENGTH), '</p>');
		// Check if position was found
		if ($index_of_last_valid_paragraph_closing_tag !== false) {
			// Cut string after the last paragraph within a valid length ends
			$page_extract = substr($page_extract, 0, $index_of_last_valid_paragraph_closing_tag);
		} else {
			// Sometimes the fist paragraph of an article is longer than the critical length and has therefore cut of the hard way
			$page_extract = substr($page_extract, 0, $CRITICAL_PAGE_EXCERPT_LENGTH) . '...';
		}
	}

	break;
}

?>

<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<title>Bookish Parakeet</title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<link rel="stylesheet" href="css/normalize.css">
		<link rel="stylesheet" href="css/main.css">

		<link href='https://fonts.googleapis.com/css?family=Cabin+Sketch:400,700' rel='stylesheet' type='text/css'>
	</head>
	<body>
		<div id="content">
			<img src="img/bookish-parakeet.jpg">

			<h1>Bookish Parakeet</h1>

			<div id="wisdom">
				<?php echo $page_extract; ?>
				<p><a href="https://en.wikipedia.org/w/index.php?curid=<?php echo $page_id; ?>" target="_blank">Read more about that...</a></p>
				
				<p id="something-else"><a href="./">Tell me something else!</a></p>
			</div>
		</div>

		<footer>
			<a href="http://www.krebas.com/bookish-parakeet/">Bookish Parakeet</a> shows an excerpt of a random article from <a href="https://www.wikipedia.org/">Wikipedia</a> | Idea comes from this <a href="https://twitter.com/OldManKris/status/673184195485790208">Tweet</a> | Fork the code on <a href="https://github.com/krewast/bookish-parakeet">Github</a> | <a href="https://www.flickr.com/photos/42244964@N03/8614125728/">Photo</a> from Flickr user <a href="https://www.flickr.com/photos/42244964@N03/">Frank Vassen</a> released under <a href="https://creativecommons.org/licenses/by/2.0/">CC BY 2.0</a> | <a href="http://www.krewast.com/imprint/">Imprint</a>
		</footer>
	</body>
</html>
