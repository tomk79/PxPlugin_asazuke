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
	 * スクレイピングを実行する
	 */
	public function scrape($fullpath_savetmpfile_to){
		$this->save_sitemap_row( $row_info );
		return true;
	}//scrape()




	/**
	 * サイトマップ行を書き出す
	 */
	private function save_sitemap_row( $row_info ){
		$sitemap_definition = $this->px->site()->get_sitemap_definition();
		$sitemap_key_list = array();
		foreach( $sitemap_definition as $row ){
			array_push( $sitemap_key_list , $row_info[$row['key']] );

		}
		$LINE = '';
		$LINE .= $this->px->dbh()->mk_csv(array($sitemap_key_list), array('charset'=>'UTF-8'));

		error_log( $LINE , 3 , $this->path_sitemap_csv );
		return true;
	}

}

?>