<?php

/**
 * オペレータ：コンテンツ
 * Copyright (C)Tomoya Koyanagi.
 */
class pxplugin_asazuke_operator_contents{

	private $px;
	private $obj_proj;

	/**
	 * コンストラクタ
	 */
	public function __construct( $px, $obj_proj ){
		$this->px = $px;
		$this->obj_proj = $obj_proj;
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
		$content = $domParser->find( $this->obj_proj->get_selector_contents_main() );
		foreach( $content as $row ){
			$content_src .= $row['innerHTML']."\n";
		}

		$content_src = preg_replace( '/\r\n|\r|\n/si', "\r\n", $content_src );

		$result = $this->px->dbh()->file_overwrite( $fullpath_save_to, $content_src );
		return $result;
	}//scrape()

}

?>