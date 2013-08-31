<?php

/**
 * オペレータ：コンテンツ
 * Copyright (C)Tomoya Koyanagi.
 */
class pxplugin_asazuke_operator_contents{

	private $px;
	private $obj_proj;
	private $path = null;

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
	private function &factory_dom_parser($path, $type = 'path'){
		$className = $this->px->load_px_plugin_class( '/asazuke/resources/PxXMLDomParser.php' );
		if( !$className ){
			$this->error_log( 'DOMパーサーのロードに失敗しました。' , __FILE__ , __LINE__ );
			return	$this->exit_process();
		}
		$obj = new $className( $path, $type );
		return	$obj;
	}

	/**
	 * スクレイピングを実行する
	 */
	public function scrape($path, $fullpath_savetmpfile_to , $fullpath_save_to){
		$this->path = $path;

		$content_src = '';

		// ヘッドセクションのソースを取得
		$content_src .= $this->get_header_src( $fullpath_savetmpfile_to );
		$content_src .= "\n"."\n";

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
	 * ヘッダー部分のソースを取得する
	 */
	private function get_header_src( $fullpath_savetmpfile_to ){
		$selectRules = $this->obj_proj->get_select_cont_subs();

		$tmpDOM = null;
		$src = '';
		$domParser = $this->factory_dom_parser($fullpath_savetmpfile_to);
		$tmpDOM = $domParser->find( 'head' );
		$header_src = $tmpDOM[0]['innerHTML'];

		$domParser = $this->factory_dom_parser($header_src, 'bin');
		// titleタグを削除
		$domParser->select('title');
		$domParser->replace( array( $this , 'callback_replace_dom_title' ) );
		// metaタグを精査
		$domParser->select('meta');
		$domParser->replace( array( $this , 'callback_replace_dom_meta' ) );
		// scriptタグを精査
		$domParser->select('script');
		$domParser->replace( array( $this , 'callback_replace_dom_script' ) );
		// linkタグを精査
		$domParser->select('link');
		$domParser->replace( array( $this , 'callback_replace_dom_link' ) );

		$header_src = $domParser->get_src();

		$src .= '<'.'?php ob_start(); ?'.'>'."\n";
		$src .= '<'.'?php /* ------ head section contents ------ */ ?'.'>'."\n";
		$src .= $header_src."\n";
		$src .= '<'.'?php $px->theme()->send_content(ob_get_clean(), '.t::data2text( 'head' ).'); ?'.'>'."\n";
		$src .= "\n";

		return $src;
	}
	/**
	 * callback: titleタグを置き換える。
	 */
	public function callback_replace_dom_title( $dom , $num ){
		// タイトルタグは削除
		// サイトマップパースのプロセスで拾っているので捨ててよし。
		return '';
	}//callback_replace_dom_title()
	/**
	 * callback: metaタグを置き換える。
	 */
	public function callback_replace_dom_meta( $dom , $num ){
		return '';
	}//callback_replace_dom_meta()
	/**
	 * callback: scriptタグを置き換える。
	 */
	public function callback_replace_dom_script( $dom , $num ){
		$src = trim($dom['attributes']['src']);
		if( !preg_match('/^\//', $src) ){
			$src = $this->px->dbh()->get_realpath( dirname($this->path).'/'.$src );
		}
		if( $this->obj_proj->is_ignore_common_resources( $src ) ){
			// 除外リソースなら削除する
			return '';
		}
		return $dom['outerHTML'];
	}//callback_replace_dom_script()
	/**
	 * callback: linkタグを置き換える。
	 */
	public function callback_replace_dom_link( $dom , $num ){
		$rel = trim(strtolower($dom['attributes']['rel']));
		switch( $rel ){
			case 'stylesheet':
				break;
			default:
				return '';
		}
		$href = trim($dom['attributes']['href']);
		if( !preg_match('/^\//', $href) ){
			$href = $this->px->dbh()->get_realpath( dirname($this->path).'/'.$href );
		}
		if( $this->obj_proj->is_ignore_common_resources( $href ) ){
			// 除外リソースなら削除する
			return '';
		}
		return $dom['outerHTML'];
	}//callback_replace_dom_link()


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