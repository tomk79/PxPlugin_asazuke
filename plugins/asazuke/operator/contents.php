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
		$content_src .= $this->get_main_contents_src( $fullpath_savetmpfile_to );
		$content_src .= "\n"."\n";

		// サブコンテンツを取得
		$content_src .= $this->get_sub_contents_src( $fullpath_savetmpfile_to );
		$content_src .= "\n"."\n";

		$content_src = preg_replace( '/\r\n|\r|\n/si', "\r\n", $content_src );//CRLFに変換

		$result = $this->px->dbh()->file_overwrite( $fullpath_save_to, $content_src );
		return $result;
	}//scrape()



	/**
	 * メインコンテンツソースを取得する
	 */
	private function get_main_contents_src( $fullpath_savetmpfile_to ){
		$domParser = $this->factory_dom_parser($fullpath_savetmpfile_to);
		$selectRules = $this->obj_proj->get_select_cont_main();

		$tmpDOM = null;
		$src = '';
		foreach( $selectRules as $ruleRow ){
			$tmpDOM = $domParser->find( $ruleRow['selector'] );
			if( is_null($tmpDOM[$ruleRow['index']]) ){
				continue;
			}
			$src .= $tmpDOM[$ruleRow['index']]['innerHTML'];
			break;
		}
		return $src;
	}

	/**
	 * サブコンテンツソースを取得する
	 */
	private function get_sub_contents_src( $fullpath_savetmpfile_to ){
		$domParser = $this->factory_dom_parser($fullpath_savetmpfile_to);
		$selectRules = $this->obj_proj->get_select_cont_subs();

		$tmpDOM = null;
		$src = '';
		foreach( $selectRules as $ruleRow ){
			$tmpDOM = $domParser->find( $ruleRow['selector'] );
			if( is_null($tmpDOM[$ruleRow['index']]) ){
				continue;
			}
			$src .= '<'.'?php ob_start(); ?'.'>'."\n";
			$src .= $tmpDOM[$ruleRow['index']]['innerHTML']."\n";
			$src .= '<'.'?php $px->theme()->send_content(ob_get_clean(), '.t::data2text( $ruleRow['cabinet_name'] ).'); ?'.'>'."\n";
			$src .= "\n";
		}
		return $src;
	}

}

?>