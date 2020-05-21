<?PHP
class SBInnovationBridge extends BridgeAbstract
{
	const NAME        = 'SBInnovation';
	const URI         = 'https://www.sb-innovation.de/';
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
		$htmlLastPage = getSimpleHTMLDOM(self::URI . 'showthread.php?t=' . $this->getInput('id') . '&page=9999')
			or returnServerError('No contents received!');
		$title = $htmlLastPage->find('h1 > span > a', 0)->innertext;

		$prevLastURL = self::URI . $htmlLastPage->find('a[rel=prev]', 0)->getAttribute('href');
		$htmlPrevLastPage = getSimpleHTMLDOM($prevLastURL) or returnServerError('No contents received!');

		$this->extractPosts($htmlPrevLastPage, $title);
		$this->extractPosts($htmlLastPage, $title);
		$this->items = array_reverse($this->items);
	}

	private function extractPosts($html, $title)
	{
		foreach ($html->find('li[id^=post_]') as $post) {
			$item = array();
			$item['title'] = $title;
			$postID = str_replace('post', '', $post->find('a.postcounter', 0)->getAttribute('name'));
			$item['uri'] = self::URI . 'showthread.php?t=' . $this->getInput('id') . '&p=' . $postID;
			$item['content'] = $post->find('div[id^=post_message_]', 0)->innertext;
			$item['author'] = $post->find('a.username > strong > span', 0)->innertext;
			$this->items[] = $item;
		}
	}
}
