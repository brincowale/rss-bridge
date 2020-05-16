<?PHP
class ElOtroLadoBridge extends BridgeAbstract {
	const NAME        = 'ElOtroLado';
	const URI         = 'https://www.elotrolado.net/';
	const DESCRIPTION = 'Returns the latest posts from a forum thread';
	const MAINTAINER  = 'brincowale';
	const PARAMETERS = array();
	const CACHE_TIMEOUT = 3600;

	public function collectData(){
		$html = getSimpleHTMLDOM(self::URI . 'showthread.php?threadid=2011153');
		
		$lastURL = $html->find('div.pages > a', -1)->getAttribute('href');
		$htmlLastPage = getSimpleHTMLDOM(self::URI . $lastURL);

		$prevLastURL = $htmlLastPage->find('div.pages > a', -1)->getAttribute('href');
		$htmlPrevLastPage = getSimpleHTMLDOM(self::URI . $prevLastURL);
		
		$this->extractPosts($htmlPrevLastPage);
		$this->extractPosts($htmlLastPage);
	}

	private function extractPosts($html) {
		foreach($html->find('div.post[id^=p]') as $post) {
			$item = array();
			$item['title'] = "";
			$item['uri'] = $post->find('a.permalink.share[itemprop=url]', 0)->getAttribute('href');
			$item['content'] = $post->find('div.message[itemprop=text]', 0)->innertext;
			$item['author'] = $post->find('span.author[itemprop=name]', 0)->innertext;
			$item['timestamp'] = $post->find('time[itemprop=dateCreated]', 0)->getAttribute('datetime');
			$this->items[] = $item;
		}
	}
}