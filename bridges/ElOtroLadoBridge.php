<?PHP
class ElOtroLadoBridge extends BridgeAbstract
{
	const NAME        = 'ElOtroLado';
	const URI         = 'https://www.elotrolado.net/';
	const DESCRIPTION = 'Returns the latest posts from a forum thread';
	const MAINTAINER  = 'brincowale';
	const PARAMETERS = array(array(
		'id' => array(
			'name' => 'Thread ID',
			'type' => 'number',
			'required' => true,
			'title' => 'Insert thread ID',
			'exampleValue' => '2011153'
		)
	));
	const CACHE_TIMEOUT = 300;

	public function collectData()
	{
		$html = getSimpleHTMLDOM(self::URI . 'showthread.php?threadid=' . $this->getInput('id')) or returnServerError('No contents received!');
		$title = $html->find('div[itemprop=mainEntity] span[itemprop=name]', 0)->innertext;

		$lastURL = $html->find('div.pages > a', -1)->getAttribute('href');
		$htmlLastPage = getSimpleHTMLDOM(self::URI . $lastURL) or returnServerError('No contents received!');

		$prevLastURL = $htmlLastPage->find('div.pages > a', -1)->getAttribute('href');
		$htmlPrevLastPage = getSimpleHTMLDOM(self::URI . $prevLastURL) or returnServerError('No contents received!');

		$this->extractPosts($htmlPrevLastPage, $title);
		$this->extractPosts($htmlLastPage, $title);
		$this->items = array_reverse($this->items);
	}

	private function extractPosts($html, $title)
	{
		foreach ($html->find('div.post[id^=p]') as $post) {
			$item = array();
			$item['title'] = $title;
			$item['uri'] = $post->find('a.permalink.share[itemprop=url]', 0)->getAttribute('href');
			$item['content'] = $post->find('div.message[itemprop=text]', 0)->innertext;
			$item['author'] = $post->find('span.author[itemprop=name]', 0)->innertext;
			$item['timestamp'] = $post->find('time[itemprop=dateCreated]', 0)->getAttribute('datetime');
			$this->items[] = $item;
		}
	}
}
