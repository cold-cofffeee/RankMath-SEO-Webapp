<?php
/**
 * WPML Integration.
 *
 * @since      1.0.256
 * @package    RankMathPro
 * @subpackage RankMathPro\ThirdParty
 * @author     Rank Math Pro <support@rankmath.com>
 */

namespace RankMathPro\ThirdParty;

use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * WPML class.
 */
class WPML {

	use Hooker;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->action( 'rank_math/sitemap/news/language', 'change_news_language', 10, 2 );
	}

	/**
	 * Change the news language based on the entity.
	 *
	 * @see https://en.wikipedia.org/wiki/List_of_ISO_639_language_codes
	 * @see https://developers.google.com/search/docs/crawling-indexing/sitemaps/news-sitemap
	 *
	 * @param string $lang   Language code.
	 * @param array  $entity Array of parts that make up this entry.
	 */
	public function change_news_language( $lang, $entity ) {
		$post = apply_filters( 'wpml_post_language_details', null, url_to_postid( $entity['loc'] ) );
		if ( ! $post ) {
			return $lang;
		}

		$language_code = $post['language_code'] ?? $lang;

		$mapping = [
			'pt-pt'   => 'pt',
			'pt-br'   => 'pt',
			'zh-hans' => 'zh-cn',
			'zh-hant' => 'zh-tw',
		];

		return $mapping[ $language_code ] ?? $language_code;
	}
}
