<?php

/**
 * オペレータ：サイトマップ
 * Copyright (C)Tomoya Koyanagi.
 */
class pxplugin_asazuke_operator_sitemap{

	private $px;
	private $path_sitemap_csv;

	/**
	 * コンストラクタ
	 */
	public function __construct( $px, $path_sitemap_csv ){
		$this->px = $px;
		$this->path_sitemap_csv = $path_sitemap_csv;
	}

	/**
	 * ファクトリ：DOMパーサー
	 */
	private function &factory_dom_parser($path){
		$className = $this->px->load_px_plugin_class( '/asazuke/resources/PxXMLDomParser.php' );
		if( !$className ){
			$this->error_log( 'DOMパーサーのロードに失敗しました。' , __FILE__ , __LINE__ );
			return	$this->exit_process();
		}
		$obj = new $className( $path , 'path' );
		return	$obj;
	}

	/**
	 * スクレイピングを実行する
	 */
	public function scrape($path, $fullpath_savetmpfile_to){
		$row_info = array();
		$row_info['path'] = preg_replace('/\/index\.html$/s', '/', $path);
		$row_info['title'] = $this->get_page_title($fullpath_savetmpfile_to);
		$row_info['list_flg'] = 1;

		$this->save_sitemap_row( $row_info );
		return true;
	}//scrape()

	/**
	 * ページタイトル を取得
	 */
	private function get_page_title($path){
		$domParser = $this->factory_dom_parser($path);
		$title = $domParser->find('title');
		return htmlspecialchars_decode($title[0]['innerHTML']);
	}


	/**
	 * サイトマップ行を書き出す
	 */
	private function save_sitemap_row( $row_info ){
		$sitemap_definition = $this->px->site()->get_sitemap_definition();
		$sitemap_val_list = array();
		foreach( $sitemap_definition as $row ){
			array_push( $sitemap_val_list , $row_info[$row['key']] );

		}
		$LINE = '';
		$LINE .= $this->px->dbh()->mk_csv(array($sitemap_val_list), array('charset'=>'UTF-8'));

		error_log( $LINE , 3 , $this->path_sitemap_csv );
		return true;
	}

}

?>