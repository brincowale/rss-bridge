<?PHP
class SkyscraperCityBridge extends BridgeAbstract
{
	const NAME        = 'Skyscraper City';
	const URI         = 'https://www.skyscrapercity.com';
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
		$htmlLastPage = getSimpleHTMLDOM(self::URI . '/threads/' . $this->getInput('id') . '/page-99999')
			or returnServerError('No contents received!');
		$title = $htmlLastPage->find('h1[qid=page-header]', 0)->innertext;

		$prevLastURL = $htmlLastPage->find('a[qid=page-nav-prev-button]', 0)->getAttribute('href');
		$htmlPrevLastPage = getSimpleHTMLDOM(self::URI . $prevLastURL) or returnServerError('No contents received!');

		$this->extractPosts($htmlPrevLastPage, $title);
		$this->extractPosts($htmlLastPage, $title);
		$this->items = array_reverse($this->items);
	}

	private function extractPosts($html, $title)
	{
		foreach ($html->find('article[id^=js-post-]') as $post) {
			$item = array();
			$item['title'] = $title;
			$item['uri'] = $post->find('a[qid=post-number]', 0)->getAttribute('href');
			$item['content'] = $post->find('article[itemprop=text]', 0)->innertext;
			$item['author'] = $post->find('a.username[itemprop=url]', 0)->innertext;
			$item['timestamp'] = $post->find('time[qid="post-date-time"]', 0)->getAttribute('datetime');
			$this->items[] = $item;
		}
	}
}
