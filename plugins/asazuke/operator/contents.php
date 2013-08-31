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
			$src .= $this->src_standard_replacement( $tmpDOM[$ruleRow['index']]['innerHTML'] );
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
			$src .= '<'.'?php /* ------ sub contents '.t::data2text( $ruleRow['cabinet_name'] ).' ------ */ ?'.'>'."\n";
			$src .= $this->src_standard_replacement( $tmpDOM[$ruleRow['index']]['innerHTML'] )."\n";
			$src .= '<'.'?php $px->theme()->send_content(ob_get_clean(), '.t::data2text( $ruleRow['cabinet_name'] ).'); ?'.'>'."\n";
			$src .= "\n";
		}
		return $src;
	}

	/**
	 * ソースの標準置換処理
	 */
	private function src_standard_replacement( $src ){
		$src = $this->dom_convert( $src );
		$src = $this->replace_strings( $src );
		return $src;
	}

	/**
	 * 文字列置換を実行する。
	 */
	private function replace_strings( $str ){
		$replaceRules = $this->obj_proj->get_replace_strings();
		foreach( $replaceRules as $ruleRow ){
			if( preg_match($ruleRow['preg_pattern'], $str) ){
				$str = preg_replace($ruleRow['preg_pattern'], $ruleRow['replace_to'], $str);
			}
		}
		return $str;
	}

	/**
	 * DOM置換を実行する。
	 */
	private function dom_convert( $str ){
		// [UTODO] 開発中
		$replaceRules = $this->obj_proj->get_dom_convert();

		return $str;
	}

}

?>