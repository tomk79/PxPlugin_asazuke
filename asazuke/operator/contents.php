<?php

/**
 * オペレータ：コンテンツ
 * Copyright (C)Tomoya Koyanagi.
 */
class pxplugin_asazuke_operator_contents{

	private $px;

	/**
	 * コンストラクタ
	 */
	public function __construct( $px ){
		$this->px = $px;
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
	public function scrape($fullpath_savetmpfile_to , $fullpath_save_to){
		$content_src = '';

		// メインコンテンツを取得
		$domParser = $this->factory_dom_parser($fullpath_savetmpfile_to);
		$content = $domParser->find('#content');
		$content_src .= $content[0]['innerHTML'];

		$result = $this->px->dbh()->file_overwrite( $fullpath_save_to, $content_src );
		return $result;
	}//scrape()

}

?>