<?php

/**
 * プロジェクト管理機能
 * Copyright (C)Tomoya Koyanagi.
 * Last Update: 12:54 2011/08/28
 */
class pxplugin_asazuke_admin{

	private $px;
	private $pcconf;
	private $cmd;

	private $local_sitemap = array();// ページ名等を定義する
	private $title = null; // 出力するページタイトル文字列

	/**
	 * コンストラクタ
	 */
	public function __construct( &$px, &$pcconf, $cmd ){
		$this->px = &$px;
		$this->pcconf = &$pcconf;
		$this->cmd = &$cmd;

		$this->set_sitemap();
	}

	/**
	 * config:設定値を取得
	 */
	public function get_conf( $key ){
		return	$this->pcconf->get_value( $key );
	}

	/**
	 * config:値を設定
	 */
	public function set_conf( $key , $val ){
		return	$this->pcconf->set_value( $key , $val );
	}

	/**
	 * ページタイトルを取得する
	 */
	public function get_page_title(){
		return $this->title;
	}

	/**
	 * 処理の開始
	 */
	public function start(){
		$cont_src = $this->start_controller();

		$title = $this->local_sitemap[':'.implode('.', $this->cmd)]['title'];
		if( strlen( $title ) ){
			$this->title = $title;
		}

		$rtn = '';
		$rtn .= $cont_src."\n";
		return $rtn;
	}
	/**
	 * コントローラ
	 */
	private function start_controller(){
		if( $this->cmd[0] == 'detail' ){
			#	プロジェクト詳細
			return	$this->page_project_detail();
		}elseif( $this->cmd[0] == 'create_proj' || $this->cmd[0] == 'edit_proj' ){
			#	プロジェクト作成/編集
			return	$this->start_edit_proj();
		}elseif( $this->cmd[0] == 'create_program' || $this->cmd[0] == 'edit_program' ){
			#	プログラム作成/編集
			return	$this->start_edit_program();
		}elseif( $this->cmd[0] == 'execute_program' ){
			#	プログラムを実行
			return	$this->start_execute_program();
		}elseif( $this->cmd[0] == 'delete_program_content' ){
			#	プログラムが書き出したコンテンツを削除する
			return	$this->start_delete_program_content();
		}elseif( $this->cmd[0] == 'delete_program' ){
			#	プログラムを削除
			return	$this->start_delete_program();
		}elseif( $this->cmd[0] == 'delete_proj' ){
			#	プロジェクトを削除
			return	$this->start_delete_proj();
		}elseif( $this->cmd[0] == 'configcheck' ){
			#	設定項目の確認
			return	$this->page_configcheck();
		}elseif( $this->cmd[0] == 'export' ){
			#	エクスポート
			return	$this->start_export();
		}
		return	$this->page_start();
	}


	/**
	 * コンテンツ内へのリンク先を調整する。
	 */
	private function href( $linkto = null ){
		if(is_null($linkto)){
			return '?PX=plugins.asazuke.'.implode('.',$this->cmd);
		}
		if($linkto == ':'){
			return '?PX=plugins.asazuke';
		}
		$rtn = preg_replace('/^\:/','?PX=plugins.asazuke.',$linkto);

		$rtn = $this->px->theme()->href( $rtn );
		return $rtn;
	}

	/**
	 * コンテンツ内へのリンクを生成する。
	 */
	private function mk_link( $linkto , $options = array() ){
		if( !strlen($options['label']) ){
			if( $this->local_sitemap[$linkto] ){
				$options['label'] = $this->local_sitemap[$linkto]['title'];
			}
		}
		$rtn = $this->href($linkto);

		$rtn = $this->px->theme()->mk_link( $rtn , $options );
		return $rtn;
	}

	/**
	 * フォームエレメントの遷移情報を生成する
	 */
	private function mk_form_defvalues($linkto = null){
		if(is_null($linkto)){
			return 'plugins.asazuke.'.implode('.',$this->cmd);
		}
		if($linkto == ':'){
			return 'plugins.asazuke';
		}
		$rtn = preg_replace('/^\:/','plugins.asazuke.',$linkto);
		$rtn = '<input type="hidden" name="PX" value="'.htmlspecialchars($rtn).'" />';
		return $rtn;
	}

	/**
	 * 見出しタグを生成する。
	 */
	private function mk_hx($label, $hx = '2'){
		return '<h'.$hx.'>'.t::h($label).'</h'.$hx.'>';
	}

	/**
	 * このコンテンツ内でのサイトマップを登録する
	 */
	private function set_sitemap(){

		$this->local_sitemap[ ':'                                                 ] = array( 'title'=>'ASAZUKE'                            );
		$this->local_sitemap[ ':create_proj'                                      ] = array( 'title'=>'新規プロジェクト作成'               );
		$this->local_sitemap[ ':configcheck'                                      ] = array( 'title'=>'設定の確認'                         );
		$this->local_sitemap[ ':export'                                           ] = array( 'title'=>'設定をエクスポート'                 );
		$this->local_sitemap[ ':detail.'.$this->cmd[1]                            ] = array( 'title'=>'プロジェクト詳細'                   );
		$this->local_sitemap[ ':edit_proj.'.$this->cmd[1]                         ] = array( 'title'=>'プロジェクト編集'                   );
		$this->local_sitemap[ ':create_program.'.$this->cmd[1]                    ] = array( 'title'=>'新規プログラム作成'                 );
		$this->local_sitemap[ ':edit_program.'.$this->cmd[1].'.'.$this->cmd[2]    ] = array( 'title'=>'プログラム編集'                     );
		$this->local_sitemap[ ':execute_program.'.$this->cmd[1].'.'.$this->cmd[2] ] = array( 'title'=>'プログラム実行'                     );
		$this->local_sitemap[ ':delete_program.'.$this->cmd[1].'.'.$this->cmd[2]  ] = array( 'title'=>'プログラム削除'                     );
		$this->local_sitemap[ ':delete_proj.'.$this->cmd[1]                       ] = array( 'title'=>'プロジェクトを削除'                 );
		$this->local_sitemap[ ':delete_program_content.'.$this->cmd[1]            ] = array( 'title'=>'プログラムコンテンツの削除'         );

		return true;
	}


	/**
	 * スタートページ
	 */
	private function page_start(){

		$RTN = '';
		$RTN .= '<p>'."\n";
		$RTN .= '	この機能は、ウェブアクセスにより、ネットワーク上のウェブサイトを巡回し保存します。<br />'."\n";
		$RTN .= '</p>'."\n";

		$project_model = &$this->pcconf->factory_model_project();
		$project_list = $project_model->get_project_list();
		if( !is_array($project_list) || !count($project_list) ){
			$RTN .= '<p>現在プロジェクトは登録されていません。</p>'."\n";
		}else{
			$RTN .= '<div class="unit">'."\n";
			$RTN .= '<table class="def" style="width:100%;">'."\n";
			$RTN .= '	<thead>'."\n";
			$RTN .= '		<tr>'."\n";
			$RTN .= '			<th>プロジェクト名</div></th>'."\n";
			$RTN .= '			<th>プロジェクトID</div></th>'."\n";
			$RTN .= '			<th>トップページURL</div></th>'."\n";
			$RTN .= '			<th>&nbsp;</div></th>'."\n";
			$RTN .= '		</tr>'."\n";
			$RTN .= '	</thead>'."\n";
			foreach( $project_list as $Line ){
				$RTN .= '	<tr>'."\n";
				$RTN .= '		<th class="left">'.$this->mk_link(':detail.'.$Line['id'],array('label'=>$Line['name'],'style'=>'inside')).'</th>'."\n";
				$RTN .= '		<td class="left">'.htmlspecialchars( $Line['id'] ).'</td>'."\n";
				$RTN .= '		<td class="left"><div style="overflow:auto; max-width:400px;">'.htmlspecialchars( $Line['path_docroot'] ).'</div></td>'."\n";
				$RTN .= '		<td class="left">'."\n";
				$RTN .= '			'.$this->mk_link(':detail.'.$Line['id'],array('label'=>'詳細','style'=>'inside'))."\n";
				$RTN .= '			'.$this->mk_link(':edit_proj.'.$Line['id'],array('label'=>'編集','style'=>'inside'))."\n";
				$RTN .= '			'.$this->mk_link(':delete_proj.'.$Line['id'],array('label'=>'削除','style'=>'inside')).''."\n";
				$RTN .= '		</td>'."\n";
				$RTN .= '	</tr>'."\n";
			}
			$RTN .= '</table>'."\n";
			$RTN .= '</div><!-- /.unit -->'."\n";
		}


		$RTN .= '<div class="more_links">'."\n";
		$RTN .= '<ul>'."\n";
		$RTN .= '	<li>'.$this->mk_link(':create_proj',array('style'=>'inside')).'</li>'."\n";
		$RTN .= '	<li>'.$this->mk_link(':export',array('style'=>'inside')).'</li>'."\n";
		$RTN .= '	<li>'.$this->mk_link(':configcheck',array('style'=>'inside')).'</li>'."\n";
		$RTN .= '</ul>'."\n";
		$RTN .= '</div><!-- /.more_links -->'."\n";

		return	$RTN;
	}

	/**
	 * プロジェクトの詳細画面
	 */
	private function page_project_detail(){

		$project_model = &$this->pcconf->factory_model_project();
		$project_model->load_project( $this->cmd[1] );

		$this->local_sitemap[ ':'.implode('.',$this->cmd) ] = array( 'title'=>'プロジェクト『'.htmlspecialchars( $project_model->get_project_name() ).'』の詳細情報' );

		$RTN = '';

		#======================================
		$RTN .= ''.$this->mk_hx( '基本情報' ).''."\n";
		$RTN .= '<table class="def" style="width:100%;">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>プロジェクト名 (プロジェクトID)</div></th>'."\n";
		$RTN .= '		<td style="width:70%;"><div><strong>'.htmlspecialchars( $project_model->get_project_name() ).'</strong> ('.htmlspecialchars( $this->cmd[1] ).')</div></td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>ドキュメントルートのパス</div></th>'."\n";
		$RTN .= '		<td style="width:70%;"><div style="overflow:auto; max-width:450px;">'.htmlspecialchars( $project_model->get_path_docroot() ).'</div></td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>スタートページのパス</div></th>'."\n";
		$RTN .= '		<td style="width:70%;"><div>'.htmlspecialchars( $project_model->get_path_startpage() ).'</div></td>'."\n";
		$RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>デフォルトのファイル名</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;"><div>'.htmlspecialchars( $project_model->get_default_filename() ).'</div></td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>URL変換時に省略するファイル名</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;"><div>'.htmlspecialchars( implode( ', ' , $project_model->get_omit_filename() ) ).'</div></td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>対象外URLリスト</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $urlList = $project_model->get_urllist_outofsite();
		// if( count( $urlList ) ){
		// 	$RTN .= '			<ul>'."\n";
		// 	foreach( $urlList as $url ){
		// 		$RTN .= '				<li>'.htmlspecialchars( $url ).'</li>'."\n";
		// 	}
		// 	$RTN .= '			</ul>'."\n";
		// }else{
		// 	$RTN .= '			<div>指定はありません。</div>'."\n";
		// }
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>追加スタートページURLリスト</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $urlList = $project_model->get_urllist_startpages();
		// if( count( $urlList ) ){
		// 	$RTN .= '			<ul>'."\n";
		// 	foreach( $urlList as $url ){
		// 		$RTN .= '				<li>'.htmlspecialchars( $url ).'</li>'."\n";
		// 	}
		// 	$RTN .= '			</ul>'."\n";
		// }else{
		// 	$RTN .= '			<div>指定はありません。</div>'."\n";
		// }
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>認証</div></th>'."\n";
		// if( !$project_model->isset_basic_authentication_info() ){
		// 	$RTN .= '		<td style="width:70%;"><div>設定なし(または無効)</div></td>'."\n";
		// }else{
		// 	$RTN .= '		<td style="width:70%;">'."\n";
		// 	$label = array( ''=>'自動選択', 'basic'=>'ベーシック認証', 'digest'=>'ダイジェスト認証' );
		// 	$RTN .= '			<div>認証タイプ: '.htmlspecialchars( $label[$project_model->get_authentication_type()] ).'</div>'."\n";
		// 	$RTN .= '			<div>ID: '.htmlspecialchars( $project_model->get_basic_authentication_id() ).'</div>'."\n";
		// 	$RTN .= '			<div>PW: ********</div>'."\n";
		// 	$RTN .= '		</td>'."\n";
		// }
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>パス指定変換</div></th>'."\n";
		// $label = array( 'relative'=>'相対パス','absolute'=>'絶対パス','url'=>'URL','none'=>'変換しない' );
		// $RTN .= '		<td style="width:70%;"><div>'.htmlspecialchars( $label[$project_model->get_path_conv_method()] ).'</div></td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>サイト外指定パスの変換</div></th>'."\n";
		// $label = array( '0'=>'パス指定変換設定に従う','1'=>'URLに変換する' );
		// $RTN .= '		<td style="width:70%;"><div>'.htmlspecialchars( $label[intval($project_model->get_outofsite2url_flg())] ).'</div></td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>未定義のパラメータ</div></th>'."\n";
		// $label = array( '0'=>'送信しない','1'=>'送信する' );
		// $RTN .= '		<td style="width:70%;"><div>'.htmlspecialchars( $label[intval($project_model->get_send_unknown_params_flg())] ).'</div></td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>フォーム送信可否</div></th>'."\n";
		// $label = array( '0'=>'送信しない','1'=>'送信する' );
		// $RTN .= '		<td style="width:70%;"><div>'.htmlspecialchars( $label[intval($project_model->get_send_form_flg())] ).'</div></td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>HTML内のJavaScript</div></th>'."\n";
		// $label = array( '0'=>'解析しない','1'=>'解析する' );
		// $RTN .= '		<td style="width:70%;"><div>'.htmlspecialchars( $label[intval($project_model->get_parse_jsinhtml_flg())] ).'</div></td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>複製先パス</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;"><div>'.htmlspecialchars( $project_model->get_path_copyto() ).'</div></td>'."\n";
		// $RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href( ':edit_proj.'.$this->cmd[1] ) ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="プロジェクト情報を編集する" /></p>'."\n";
		$RTN .= '</form>'."\n";

		#======================================
		$RTN .= ''.$this->mk_hx( 'プログラム一覧' ).''."\n";

		$program_list = $project_model->get_program_list();

		$CSS = '';
		$CSS .= '#content .cont_unit_program{'."\n";
		$CSS .= '	border:2px solid #ff9999;'."\n";
		$CSS .= '}'."\n";
		$RTN .= '<style type="text/css">'.$CSS.'</style>'."\n";

		$RTN .= '<div class="unit cont_unit_program">'."\n";
		if( !is_array( $program_list ) || !count( $program_list ) ){
			$RTN .= '<p>現在、プログラムは登録されていません。</p>'."\n";
		}else{
			$RTN .= '<table class="def" style="width:100%;">'."\n";
			$RTN .= '	<thead>'."\n";
			$RTN .= '		<tr>'."\n";
			$RTN .= '			<th><div style="overflow:hidden;">プログラム名</div></th>'."\n";
			$RTN .= '			<th><div style="overflow:hidden;">プログラムID</div></th>'."\n";
			$RTN .= '			<th><div style="overflow:hidden;">パラメータ</div></th>'."\n";
			$RTN .= '			<th><div style="overflow:hidden;">HTTP_USER_AGENT</div></th>'."\n";
			$RTN .= '			<th><div>&nbsp;</div></th>'."\n";
			$RTN .= '			<th><div>&nbsp;</div></th>'."\n";
			$RTN .= '			<th><div>&nbsp;</div></th>'."\n";
			$RTN .= '		</tr>'."\n";
			$RTN .= '	</thead>'."\n";
			foreach( $program_list as $program_id ){
				$program_model = &$project_model->factory_program( $program_id );
				$RTN .= '	<tr>'."\n";
				$RTN .= '		<th><div style="overflow:hidden;">'.$this->mk_link(':execute_program.'.$this->cmd[1].'.'.$program_model->get_program_id(),array('label'=>$program_model->get_program_name(),'style'=>'inside')).'</div></th>'."\n";
				$RTN .= '		<td><div style="overflow:hidden;">'.htmlspecialchars( $program_model->get_program_id() ).'</div></td>'."\n";
				$RTN .= '		<td><div style="overflow:hidden;">'.htmlspecialchars( $program_model->get_program_param() ).'</div></td>'."\n";
				$RTN .= '		<td><div style="overflow:hidden;">'.htmlspecialchars( $program_model->get_program_useragent() ).'</div></td>'."\n";
				$RTN .= '		<td><div class="center">'.$this->mk_link(':edit_program.'.$this->cmd[1].'.'.$program_model->get_program_id(),array('label'=>'編集')).'</div></td>'."\n";
				$RTN .= '		<td><div class="center">'.$this->mk_link(':execute_program.'.$this->cmd[1].'.'.$program_model->get_program_id(),array('label'=>'実行')).'</div></td>'."\n";
				$RTN .= '		<td><div class="center">'.$this->mk_link(':delete_program.'.$this->cmd[1].'.'.$program_model->get_program_id(),array('label'=>'削除')).'</div></td>'."\n";
				$RTN .= '	</tr>'."\n";
			}
			$RTN .= '</table>'."\n";
		}
		$RTN .= '</div><!-- / .cont_unit_program -->'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href( ':create_program.'.$this->cmd[1] ) ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="新規プログラムを追加する" /></p>'."\n";
		$RTN .= '</form>'."\n";

		$RTN .= '<div class="more_links">'."\n";
		$RTN .= '	<ul>'."\n";
		$RTN .= '		<li>'.$this->mk_link(':delete_proj.'.$this->cmd[1],array('label'=>'このプロジェクトを削除','style'=>'inside')).'</li>'."\n";
		$RTN .= '		<li><a href="'.t::h($this->href(':')).'">戻る</a></li>'."\n";
		$RTN .= '	</ul>'."\n";
		$RTN .= '</div><!-- /.more_links -->'."\n";
		$RTN .= ''."\n";


		return	$RTN;
	}



	###################################################################################################################

	/**
	 * 新規プロジェクト作成/編集
	 */
	private function start_edit_proj(){
		$error = $this->check_edit_proj_check();
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_edit_proj_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'confirm' && !count( $error ) ){
			return	$this->page_edit_proj_confirm();
		}elseif( $this->px->req()->get_param('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_edit_proj_execute();
		}elseif( !strlen( $this->px->req()->get_param('mode') ) ){
			$error = array();
			if( $this->cmd[0] == 'edit_proj' ){
				$project_model = &$this->pcconf->factory_model_project();
				$project_model->load_project( $this->cmd[1] );
				$this->px->req()->set_param( 'project_id' , $this->cmd[1] );
				$this->px->req()->set_param( 'project_name' , $project_model->get_project_name() );
				$this->px->req()->set_param( 'path_stargpage' , $project_model->get_path_startpage() );
				$this->px->req()->set_param( 'path_docroot' , $project_model->get_path_docroot() );
				// $this->px->req()->set_param( 'default_filename' , $project_model->get_default_filename() );
				// $this->px->req()->set_param( 'omit_filename' , implode( ',' , $project_model->get_omit_filename() ) );
				// $this->px->req()->set_param( 'outofsite2url_flg' , $project_model->get_outofsite2url_flg() );
				// $this->px->req()->set_param( 'send_unknown_params_flg' , intval( $project_model->get_send_unknown_params_flg() ) );
				// $this->px->req()->set_param( 'send_form_flg' , intval( $project_model->get_send_form_flg() ) );
				// $this->px->req()->set_param( 'parse_jsinhtml_flg' , intval( $project_model->get_parse_jsinhtml_flg() ) );
				// $this->px->req()->set_param( 'path_copyto' , $project_model->get_path_copyto() );
				// $urllist_outofsite = $project_model->get_urllist_outofsite();
				// $str_urllist = '';
				// foreach( $urllist_outofsite as $url ){
				// 	$str_urllist .= $url."\n";
				// }
				// $this->px->req()->set_param( 'urllist_outofsite' , $str_urllist );

				// $urllist_startpages = $project_model->get_urllist_startpages();
				// $str_urllist = '';
				// foreach( $urllist_startpages as $url ){
				// 	$str_urllist .= $url."\n";
				// }
				// $this->px->req()->set_param( 'urllist_startpages' , $str_urllist );

				// $this->px->req()->set_param( 'authentication_type' , $project_model->get_authentication_type() );
				// $this->px->req()->set_param( 'basic_authentication_id' , $project_model->get_basic_authentication_id() );
				// $this->px->req()->set_param( 'basic_authentication_pw' , $project_model->get_basic_authentication_pw() );

				// $this->px->req()->set_param( 'path_conv_method' , $project_model->get_path_conv_method() );
			}else{
				#	新規作成のデフォルト値
				$this->px->req()->set_param('default_filename','index.html');
			}
		}
		return	$this->page_edit_proj_input( $error );
	}
	/**
	 * 新規プロジェクト作成/編集：入力
	 */
	private function page_edit_proj_input( $error ){
		$RTN = ''."\n";

		$RTN .= '<p>'."\n";
		$RTN .= '	プロジェクトの情報を入力して、「確認する」ボタンをクリックしてください。<span class="form_elements-must">必須</span>印の項目は必ず入力してください。<br />'."\n";
		$RTN .= '</p>'."\n";
		if( is_array( $error ) && count( $error ) ){
			$RTN .= '<p class="error">'."\n";
			$RTN .= '	入力エラーを検出しました。画面の指示に従って修正してください。<br />'."\n";
			$RTN .= '</p>'."\n";
		}
		$RTN .= '<form action="'.htmlspecialchars( $this->href() ).'" method="post">'."\n";
		$RTN .= '<table style="width:100%;" class="form_elements">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>プロジェクトID <span class="form_elements-must">必須</span></div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		if( $this->cmd[0] == 'edit_proj' ){
			#	編集
			$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('project_id') ).'<input type="hidden" name="project_id" value="'.htmlspecialchars( $this->px->req()->get_param('project_id') ).'" /></div>'."\n";
		}else{
			#	新規
			$RTN .= '			<div><input type="text" name="project_id" value="'.htmlspecialchars( $this->px->req()->get_param('project_id') ).'" /></div>'."\n";
			if( strlen( $error['project_id'] ) ){
				$RTN .= '			<div class="error">'.$error['project_id'].'</div>'."\n";
			}
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>プロジェクト名 <span class="form_elements-must">必須</span></div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div><input type="text" name="project_name" value="'.htmlspecialchars( $this->px->req()->get_param('project_name') ).'" style="width:80%;" /></div>'."\n";
		if( strlen( $error['project_name'] ) ){
			$RTN .= '			<div class="error">'.$error['project_name'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>ドキュメントルートのパス <span class="form_elements-must">必須</span></div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div><input type="text" name="path_docroot" value="'.htmlspecialchars( $this->px->req()->get_param('path_docroot') ).'" style="width:80%;" /></div>'."\n";
		if( strlen( $error['path_docroot'] ) ){
			$RTN .= '			<div class="error">'.$error['path_docroot'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>スタートページのパス <span class="form_elements-must">必須</span></div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div><input type="text" name="path_stargpage" value="'.htmlspecialchars( $this->px->req()->get_param('path_stargpage') ).'" style="width:80%;" /></div>'."\n";
		if( strlen( $error['path_stargpage'] ) ){
			$RTN .= '			<div class="error">'.$error['path_stargpage'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>デフォルトのファイル名 <span class="form_elements-must">必須</span></div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $RTN .= '			<div><input type="text" name="default_filename" value="'.htmlspecialchars( $this->px->req()->get_param('default_filename') ).'" style="width:80%;" /></div>'."\n";
		// if( strlen( $error['default_filename'] ) ){
		// 	$RTN .= '			<div class="error">'.$error['default_filename'].'</div>'."\n";
		// }
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>URL変換時に省略するファイル名</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $RTN .= '			<div><input type="text" name="omit_filename" value="'.htmlspecialchars( $this->px->req()->get_param('omit_filename') ).'" style="width:80%;" /></div>'."\n";
		// if( strlen( $error['omit_filename'] ) ){
		// 	$RTN .= '			<div class="error">'.$error['omit_filename'].'</div>'."\n";
		// }
		// $RTN .= '			<ul class="form_elements-notes">'."\n";
		// $RTN .= '				<li>※ファイル名は完全一致で評価されます。</li>'."\n";
		// $RTN .= '				<li>※カンマ区切りで複数登録することができます。</li>'."\n";
		// $RTN .= '			</ul>'."\n";
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>対象外URLリスト</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $RTN .= '			<div><textarea name="urllist_outofsite" rows="9">'.htmlspecialchars( $this->px->req()->get_param('urllist_outofsite') ).'</textarea></div>'."\n";
		// if( strlen( $error['urllist_outofsite'] ) ){
		// 	$RTN .= '			<div class="error">'.$error['urllist_outofsite'].'</div>'."\n";
		// }
		// $RTN .= '			<ul class="form_elements-notes">'."\n";
		// $RTN .= '				<li>※プロトコル部(http://またはhttps://)から始まる完全なURLで指定してください。</li>'."\n";
		// $RTN .= '				<li>※改行区切りで複数登録することができます。</li>'."\n";
		// $RTN .= '				<li>※アスタリスク(*)記号でワイルドカードを表現できます。</li>'."\n";
		// $RTN .= '			</ul>'."\n";
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>追加スタートページURLリスト</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $RTN .= '			<div><textarea name="urllist_startpages" rows="9">'.htmlspecialchars( $this->px->req()->get_param('urllist_startpages') ).'</textarea></div>'."\n";
		// if( strlen( $error['urllist_startpages'] ) ){
		// 	$RTN .= '			<div class="error">'.$error['urllist_startpages'].'</div>'."\n";
		// }
		// $RTN .= '			<ul class="form_elements-notes">'."\n";
		// $RTN .= '				<li>※プロトコル部(http://またはhttps://)から始まる完全なURLで指定してください。</li>'."\n";
		// $RTN .= '				<li>※改行区切りで複数登録することができます。</li>'."\n";
		// $RTN .= '			</ul>'."\n";
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>認証</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $RTN .= '			<div>認証タイプ : '."\n";
		// $c = array( $this->px->req()->get_param('authentication_type')=>' selected="selected"' );
		// $RTN .= '				<select name="authentication_type">'."\n";
		// $RTN .= '					<option value=""'.$c[''].'>自動選択</option>'."\n";
		// $RTN .= '					<option value="basic"'.$c['basic'].'>ベーシック認証</option>'."\n";
		// $RTN .= '					<option value="digest"'.$c['digest'].'>ダイジェスト認証</option>'."\n";
		// $RTN .= '				</select>'."\n";
		// $RTN .= '			</div>'."\n";
		// $RTN .= '			<div>ID : <input type="text" name="basic_authentication_id" value="'.htmlspecialchars( $this->px->req()->get_param('basic_authentication_id') ).'" /></div>'."\n";
		// if( strlen( $error['basic_authentication_id'] ) ){
		// 	$RTN .= '			<div class="error">'.$error['basic_authentication_id'].'</div>'."\n";
		// }
		// $RTN .= '			<div>PW : <input type="text" name="basic_authentication_pw" value="'.htmlspecialchars( $this->px->req()->get_param('basic_authentication_pw') ).'" /></div>'."\n";
		// if( strlen( $error['basic_authentication_pw'] ) ){
		// 	$RTN .= '			<div class="error">'.$error['basic_authentication_pw'].'</div>'."\n";
		// }
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>パス指定変換</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $RTN .= '			<div>'."\n";
		// $c = array( $this->px->req()->get_param('path_conv_method')=>' selected="selected"' );
		// $RTN .= '				<select name="path_conv_method">'."\n";
		// $RTN .= '					<option value="relative"'.$c['relative'].'>相対パス</option>'."\n";
		// $RTN .= '					<option value="absolute"'.$c['absolute'].'>絶対パス</option>'."\n";
		// $RTN .= '					<option value="url"'.$c['url'].'>URL</option>'."\n";
		// $RTN .= '					<option value="none"'.$c['none'].'>変換しない</option>'."\n";
		// $RTN .= '				</select>'."\n";
		// $RTN .= '			</div>'."\n";
		// if( strlen( $error['path_conv_method'] ) ){
		// 	$RTN .= '			<div class="error">'.$error['path_conv_method'].'</div>'."\n";
		// }
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>サイト外指定パスの変換</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $RTN .= '			<div>'."\n";
		// $c = array( $this->px->req()->get_param('outofsite2url_flg')=>' selected="selected"' );
		// $RTN .= '				<select name="outofsite2url_flg">'."\n";
		// $RTN .= '					<option value="0"'.$c['0'].'>パス指定変換設定に従う</option>'."\n";
		// $RTN .= '					<option value="1"'.$c['1'].'>URLに変換する</option>'."\n";
		// $RTN .= '				</select>'."\n";
		// $RTN .= '			</div>'."\n";
		// if( strlen( $error['outofsite2url_flg'] ) ){
		// 	$RTN .= '			<div class="error">'.$error['outofsite2url_flg'].'</div>'."\n";
		// }
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>未定義のパラメータ</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $RTN .= '			<div>'."\n";
		// $c = array( $this->px->req()->get_param('send_unknown_params_flg')=>' selected="selected"' );
		// $RTN .= '				<select name="send_unknown_params_flg">'."\n";
		// $RTN .= '					<option value="0"'.$c['0'].'>送信しない</option>'."\n";
		// $RTN .= '					<option value="1"'.$c['1'].'>送信する</option>'."\n";
		// $RTN .= '				</select>'."\n";
		// $RTN .= '			</div>'."\n";
		// if( strlen( $error['send_unknown_params_flg'] ) ){
		// 	$RTN .= '			<div class="error">'.$error['send_unknown_params_flg'].'</div>'."\n";
		// }
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>フォーム送信可否</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $RTN .= '			<div>'."\n";
		// $c = array( $this->px->req()->get_param('send_form_flg')=>' selected="selected"' );
		// $RTN .= '				<select name="send_form_flg">'."\n";
		// $RTN .= '					<option value="0"'.$c['0'].'>送信しない</option>'."\n";
		// $RTN .= '					<option value="1"'.$c['1'].'>送信する</option>'."\n";
		// $RTN .= '				</select>'."\n";
		// $RTN .= '			</div>'."\n";
		// if( strlen( $error['send_form_flg'] ) ){
		// 	$RTN .= '			<div class="error">'.$error['send_form_flg'].'</div>'."\n";
		// }
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>HTML内のJavaScript</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $RTN .= '			<div>'."\n";
		// $c = array( $this->px->req()->get_param('parse_jsinhtml_flg')=>' selected="selected"' );
		// $RTN .= '				<select name="parse_jsinhtml_flg">'."\n";
		// $RTN .= '					<option value="0"'.$c['0'].'>解析しない</option>'."\n";
		// $RTN .= '					<option value="1"'.$c['1'].'>解析する</option>'."\n";
		// $RTN .= '				</select>'."\n";
		// $RTN .= '			</div>'."\n";
		// if( strlen( $error['parse_jsinhtml_flg'] ) ){
		// 	$RTN .= '			<div class="error">'.$error['parse_jsinhtml_flg'].'</div>'."\n";
		// }
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>複製先パス</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $RTN .= '			<div><input type="text" name="path_copyto" value="'.htmlspecialchars( $this->px->req()->get_param('path_copyto') ).'" style="width:80%;" /></div>'."\n";
		// if( strlen( $error['path_copyto'] ) ){
		// 	$RTN .= '			<div class="error">'.$error['path_copyto'].'</div>'."\n";
		// }
		// $RTN .= '			<ul class="form_elements-notes">'."\n";
		// $RTN .= '				<li>※収集完了後に、収集したコンテンツを複製することができます。複製しない場合は、空白に設定してください。</li>'."\n";
		// $RTN .= '				<li>※複製先パスは、既に存在するパスである必要があります。</li>'."\n";
		// $RTN .= '			</ul>'."\n";
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";
		$RTN .= '	<div class="center"><input type="submit" value="確認する" /></div>'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="confirm" />'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	/**
	 * 新規プロジェクト作成/編集：確認
	 */
	private function page_edit_proj_confirm(){
		$RTN = ''."\n";
		$HIDDEN = ''."\n";

		$RTN .= '<p>'."\n";
		$RTN .= '	入力した内容を確認してください。<br />'."\n";
		$RTN .= '</p>'."\n";

		$RTN .= '<table style="width:100%;" class="form_elements">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>プロジェクトID</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('project_id') ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="project_id" value="'.htmlspecialchars( $this->px->req()->get_param('project_id') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>プロジェクト名</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('project_name') ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="project_name" value="'.htmlspecialchars( $this->px->req()->get_param('project_name') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>ドキュメントルートのパス</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div style="overflow:hidden; max-width:300px;">'.htmlspecialchars( $this->px->req()->get_param('path_docroot') ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="path_docroot" value="'.htmlspecialchars( $this->px->req()->get_param('path_docroot') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>スタートページのパス</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('path_stargpage') ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="path_stargpage" value="'.htmlspecialchars( $this->px->req()->get_param('path_stargpage') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>デフォルトのファイル名</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $RTN .= '			<div>'.t::text2html( $this->px->req()->get_param('default_filename') ).'</div>'."\n";
		// $HIDDEN .= '<input type="hidden" name="default_filename" value="'.htmlspecialchars( $this->px->req()->get_param('default_filename') ).'" />';
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>URL変換時に省略するファイル名</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $RTN .= '			<div>'.t::text2html( $this->px->req()->get_param('omit_filename') ).'</div>'."\n";
		// $HIDDEN .= '<input type="hidden" name="omit_filename" value="'.htmlspecialchars( $this->px->req()->get_param('omit_filename') ).'" />';
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>対象外URLリスト</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $RTN .= '			<div>'.t::text2html( $this->px->req()->get_param('urllist_outofsite') ).'</div>'."\n";
		// $HIDDEN .= '<input type="hidden" name="urllist_outofsite" value="'.htmlspecialchars( $this->px->req()->get_param('urllist_outofsite') ).'" />';
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>追加スタートページURLリスト</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $RTN .= '			<div>'.t::text2html( $this->px->req()->get_param('urllist_startpages') ).'</div>'."\n";
		// $HIDDEN .= '<input type="hidden" name="urllist_startpages" value="'.htmlspecialchars( $this->px->req()->get_param('urllist_startpages') ).'" />';
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>認証</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $label = array( ''=>'自動選択', 'basic'=>'ベーシック認証', 'digest'=>'ダイジェスト認証' );
		// $RTN .= '			<div>認証タイプ： '.htmlspecialchars( $label[$this->px->req()->get_param('authentication_type')] ).'</div>'."\n";
		// $RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('basic_authentication_id') ).' : '.htmlspecialchars( $this->px->req()->get_param('basic_authentication_pw') ).'</div>'."\n";
		// $HIDDEN .= '<input type="hidden" name="authentication_type" value="'.htmlspecialchars( $this->px->req()->get_param('authentication_type') ).'" />';
		// $HIDDEN .= '<input type="hidden" name="basic_authentication_id" value="'.htmlspecialchars( $this->px->req()->get_param('basic_authentication_id') ).'" />';
		// $HIDDEN .= '<input type="hidden" name="basic_authentication_pw" value="'.htmlspecialchars( $this->px->req()->get_param('basic_authentication_pw') ).'" />';
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>パス指定変換</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $label = array( 'relative'=>'相対パス','absolute'=>'絶対パス','url'=>'URL','none'=>'変換しない' );
		// $RTN .= '			<div>'.htmlspecialchars( $label[$this->px->req()->get_param('path_conv_method')] ).'</div>'."\n";
		// $HIDDEN .= '<input type="hidden" name="path_conv_method" value="'.htmlspecialchars( $this->px->req()->get_param('path_conv_method') ).'" />';
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>サイト外指定パスの変換</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $label = array( '0'=>'パス指定変換設定に従う','1'=>'URLに変換する' );
		// $RTN .= '			<div>'.htmlspecialchars( $label[$this->px->req()->get_param('outofsite2url_flg')] ).'</div>'."\n";
		// $HIDDEN .= '<input type="hidden" name="outofsite2url_flg" value="'.htmlspecialchars( $this->px->req()->get_param('outofsite2url_flg') ).'" />';
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>未定義のパラメータ</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $label = array( '0'=>'送信しない','1'=>'送信する' );
		// $RTN .= '			<div>'.htmlspecialchars( $label[$this->px->req()->get_param('send_unknown_params_flg')] ).'</div>'."\n";
		// $HIDDEN .= '<input type="hidden" name="send_unknown_params_flg" value="'.htmlspecialchars( $this->px->req()->get_param('send_unknown_params_flg') ).'" />';
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>フォーム送信可否</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $label = array( '0'=>'送信しない','1'=>'送信する' );
		// $RTN .= '			<div>'.htmlspecialchars( $label[$this->px->req()->get_param('send_form_flg')] ).'</div>'."\n";
		// $HIDDEN .= '<input type="hidden" name="send_form_flg" value="'.htmlspecialchars( $this->px->req()->get_param('send_form_flg') ).'" />';
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>HTML内のJavaScript</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $label = array( '0'=>'解析しない','1'=>'解析する' );
		// $RTN .= '			<div>'.htmlspecialchars( $label[$this->px->req()->get_param('parse_jsinhtml_flg')] ).'</div>'."\n";
		// $HIDDEN .= '<input type="hidden" name="parse_jsinhtml_flg" value="'.htmlspecialchars( $this->px->req()->get_param('parse_jsinhtml_flg') ).'" />';
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;"><div>複製先パス</div></th>'."\n";
		// $RTN .= '		<td style="width:70%;">'."\n";
		// $RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('path_copyto') ).'</div>'."\n";
		// $HIDDEN .= '<input type="hidden" name="path_copyto" value="'.htmlspecialchars( $this->px->req()->get_param('path_copyto') ).'" />';
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<div class="unit">'."\n";
		$RTN .= '<div class="center">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.''."\n";
		$RTN .= '	<input type="submit" value="保存する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="input" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.''."\n";
		$RTN .= '	<input type="submit" value="訂正する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<div class="unit">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href(':') ).'" method="post">'."\n";
		$RTN .= '	<div class="center"><input type="submit" value="キャンセル" /></div>'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		return	$RTN;
	}
	/**
	 * 新規プロジェクト作成/編集：チェック
	 */
	private function check_edit_proj_check(){
		$RTN = array();
		if( !strlen( $this->px->req()->get_param('project_id') ) ){
			$RTN['project_id'] = 'プロジェクトIDは必須項目です。';
		}elseif( preg_match( '/\r\n|\r|\n/' , $this->px->req()->get_param('project_id') ) ){
			$RTN['project_id'] = 'プロジェクトIDに改行を含めることはできません。';
		}elseif( strlen( $this->px->req()->get_param('project_id') ) > 64 ){
			$RTN['project_id'] = 'プロジェクトIDが長すぎます。';
		}elseif( !preg_match( '/^[a-z0-9\_\-\.\@]+$/' , $this->px->req()->get_param('project_id') ) ){
			$RTN['project_id'] = 'プロジェクトIDに使用できない文字が含まれています。';
		}
		if( !strlen( $this->px->req()->get_param('project_name') ) ){
			$RTN['project_name'] = 'プロジェクト名は必須項目です。';
		}elseif( preg_match( '/\r\n|\r|\n/' , $this->px->req()->get_param('project_name') ) ){
			$RTN['project_name'] = 'プロジェクト名に改行を含めることはできません。';
		}elseif( strlen( $this->px->req()->get_param('project_name') ) > 256 ){
			$RTN['project_name'] = 'プロジェクト名が長すぎます。';
		}

		// if( !strlen( $this->px->req()->get_param('default_filename') ) ){
		// 	$RTN['default_filename'] = 'デフォルトのファイル名は必須項目です。';
		// }elseif( preg_match( '/\r\n|\r|\n/' , $this->px->req()->get_param('default_filename') ) ){
		// 	$RTN['default_filename'] = 'デフォルトのファイル名に改行を含めることはできません。';
		// }

		// if( preg_match( '/\r\n|\r|\n/' , $this->px->req()->get_param('omit_filename') ) ){
		// 	$RTN['omit_filename'] = 'URL変換時に省略するファイル名に改行を含めることはできません。';
		// }

		if( strlen($this->px->req()->get_param('path_docroot')) ){
			$tmp_val = $this->px->req()->get_param('path_docroot');
			$tmp_val = preg_replace('/\\\\/','/',$tmp_val);//バックスラッシュをスラッシュに置換
			$tmp_val = preg_replace('/^[a-zA-Z]\:\//s','/',$tmp_val);//ボリュームラベルを削除
			$this->px->req()->set_param('path_docroot', $tmp_val);
		}
		if( !strlen( $this->px->req()->get_param('path_docroot') ) ){
			$RTN['path_docroot'] = 'ドキュメントルートのパスは必須項目です。';
		}elseif( preg_match( '/\r\n|\r|\n/' , $this->px->req()->get_param('path_docroot') ) ){
			$RTN['path_docroot'] = 'ドキュメントルートのパスはに改行を含めることはできません。';
		}elseif( !$this->px->dbh()->is_dir( $this->px->req()->get_param('path_docroot') ) ){
			$RTN['path_docroot'] = 'ドキュメントルートのパスのディレクトリが存在しません。';
		}

		if( strlen($this->px->req()->get_param('path_stargpage')) ){
			$tmp_val = $this->px->req()->get_param('path_stargpage');
			$tmp_val = preg_replace('/\\\\/','/',$tmp_val);//バックスラッシュをスラッシュに置換
			$tmp_val = preg_replace('/^[a-zA-Z]\:\//s','/',$tmp_val);//ボリュームラベルを削除
			$tmp_val = preg_replace('/\/index\.html$/s','/',$tmp_val);//ファイル名 index.html を省略
			$this->px->req()->set_param('path_stargpage', $tmp_val);
		}
		if( !strlen( $this->px->req()->get_param('path_stargpage') ) ){
			$RTN['path_stargpage'] = 'スタートページのパスは必須項目です。';
		}elseif( preg_match( '/\r\n|\r|\n/' , $this->px->req()->get_param('path_stargpage') ) ){
			$RTN['path_stargpage'] = 'スタートページのパスはに改行を含めることはできません。';
		}elseif( !$this->px->dbh()->is_file( $this->px->req()->get_param('path_docroot').'/'.$this->px->req()->get_param('path_stargpage') ) && !$this->px->dbh()->is_file( $this->px->req()->get_param('path_docroot').'/'.$this->px->req()->get_param('path_stargpage').'/index.html' ) ){
			$RTN['path_stargpage'] = 'スタートページのパスのファイルが存在しません。';
		}
		// switch( $this->px->req()->get_param('path_conv_method') ){
		// 	case 'relative':
		// 	case 'absolute':
		// 	case 'url':
		// 	case 'none':
		// 		break;
		// 	default:
		// 		$RTN['path_conv_method'] = '選択できない値を選びました。';
		// 		break;
		// }

		// if( strlen( $this->px->req()->get_param('path_copyto') ) ){
		// 	if( !is_dir( $this->px->req()->get_param('path_copyto') ) ){
		// 		$RTN['path_copyto'] = '複製先パスには、ディレクトリが存在している必要があります。';
		// 	// }elseif( !$this->dbh->check_rootdir( $this->px->req()->get_param('path_copyto') ) ){
		// 	// 	$RTN['path_copyto'] = '複製先パスが、フレームワークの管理外のパスを指しています。';
		// 	}
		// }
		return	$RTN;
	}
	/**
	 * 新規プロジェクト作成/編集：実行
	 */
	private function execute_edit_proj_execute(){
		// if( !$this->user->save_t_lastaction() ){
		// 	#	2重書き込み防止
		// 	return $this->px->redirect( $this->href().'&mode=thanks' );
		// }

		$project_model = &$this->pcconf->factory_model_project();

		if( $this->cmd[0] == 'edit_proj' ){
			#	既存プロジェクトの編集
			$project_model->load_project( $this->cmd[1] );
		}elseif( $this->cmd[0] == 'create_proj' ){
			#	新規プロジェクト作成
			if( !$project_model->create_new_project( $this->px->req()->get_param('project_id') ) ){
				return	'<p class="error">新規プロジェクトの作成に失敗しました。</p>';
			}
		}

		$project_model->set_project_name( $this->px->req()->get_param('project_name') );
		$project_model->set_path_startpage( $this->px->req()->get_param('path_stargpage') );
		$project_model->set_path_docroot( $this->px->req()->get_param('path_docroot') );
		// $project_model->set_default_filename( $this->px->req()->get_param('default_filename') );
		// $project_model->set_omit_filename( $this->px->req()->get_param('omit_filename') );
		// $project_model->set_urllist_outofsite( $this->px->req()->get_param('urllist_outofsite') );
		// $project_model->set_urllist_startpages( $this->px->req()->get_param('urllist_startpages') );
		// $project_model->set_authentication_type( $this->px->req()->get_param('authentication_type') );
		// $project_model->set_basic_authentication_id( $this->px->req()->get_param('basic_authentication_id') );
		// $project_model->set_basic_authentication_pw( $this->px->req()->get_param('basic_authentication_pw') );
		// $project_model->set_path_conv_method( $this->px->req()->get_param('path_conv_method') );
		// $project_model->set_outofsite2url_flg( $this->px->req()->get_param('outofsite2url_flg') );
		// $project_model->set_send_unknown_params_flg( $this->px->req()->get_param('send_unknown_params_flg') );
		// $project_model->set_send_form_flg( $this->px->req()->get_param('send_form_flg') );
		// $project_model->set_parse_jsinhtml_flg( $this->px->req()->get_param('parse_jsinhtml_flg') );
		// $project_model->set_path_copyto( $this->px->req()->get_param('path_copyto') );

		#	出来上がったプロジェクトを保存
		if( !$project_model->save_project() ){
			return	'<p class="error">プロジェクトの保存に失敗しました。</p>';
		}

		return $this->px->redirect( $this->href().'&mode=thanks' );
	}
	/**
	 * 新規プロジェクト作成/編集：完了
	 */
	private function page_edit_proj_thanks(){
		$RTN = ''."\n";
		if( $this->cmd[0] == 'edit_proj' ){
			$RTN .= '<p>プロジェクト編集処理を完了しました。</p>'."\n";
			$backTo = ':detail.'.$this->cmd[1];
		}else{
			$RTN .= '<p>新規プロジェクトを作成しました。</p>'."\n";
			$backTo = ':';
		}
		$RTN .= '<form action="'.htmlspecialchars( $this->href( $backTo ) ).'" method="post">'."\n";
		// $RTN .= '	'.$this->mk_form_defvalues( $backTo )."\n";
		$RTN .= '	<p><input type="submit" value="戻る" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}


	###################################################################################################################

	/**
	 * プロジェクトをエクスポート
	 */
	private function start_export(){
		$error = $this->check_export_check();
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_export_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'confirm' && !count( $error ) ){
			return	$this->page_export_confirm();
		}elseif( $this->px->req()->get_param('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_export_execute();
		}elseif( !strlen( $this->px->req()->get_param('mode') ) ){
			$error = array();
			$project_model = &$this->pcconf->factory_model_project();
			if( !count( $this->px->req()->set_param( 'project' ) ) ){
				$project_list = $project_model->get_project_list();
				$tmpAry = array();
				foreach( $project_list as $Line ){
					$tmpAry[$Line['id']] = 1;
				}
				$this->px->req()->set_param( 'project' , $tmpAry );
			}
		}
		return	$this->page_export_input( $error );
	}
	/**
	 * プロジェクトをエクスポート：入力
	 */
	private function page_export_input( $error ){
		$project_model = &$this->pcconf->factory_model_project();
		$RTN = '';

		$RTN .= '<p>'."\n";
		$RTN .= '	必要事項を入力して、「確認する」ボタンをクリックしてください。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<p>'."\n";
		$RTN .= '	 <span class="form_elements-must">必須</span> が付いている項目は必ず入力してください。<br />'."\n";
		$RTN .= '</p>'."\n";

		$RTN .= '<form action="'.htmlspecialchars( $this->href() ).'" method="post">'."\n";
		$RTN .= '<table style="width:100%;" class="form_elements">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>対象プロジェクト <span class="form_elements-must">必須</span></div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<ul class="form_elements-list">'."\n";
		$project_list = $project_model->get_project_list();
		foreach( $project_list as $Line ){
			$in_project = $this->px->req()->get_param('project');
			$c = array( 1=>' checked="checked"' );
			$RTN .= '				<li><label><input type="checkbox" name="project['.htmlspecialchars($Line['id']).']" value="1"'.$c[$in_project[$Line['id']]].' /> '.htmlspecialchars($Line['name']).' ('.htmlspecialchars($Line['id']).')</label></li>'."\n";
		}
		$RTN .= '			</ul>'."\n";
		if( strlen( $error['project'] ) ){
			$RTN .= '<div class="error">'.$error['project'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>圧縮形式 <span class="form_elements-must">必須</span></div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$is_zip = array();
		if( class_exists( 'ZipArchive' ) ){
			$is_zip['zip'] = true;
		}
		if( strlen( $this->pcconf->get_path_command('tar') ) ){
			$is_zip['tgz'] = true;
		}
		if( count( $is_zip ) ){
			$RTN .= '<ul class="form_elements-list">'."\n";
			$c = array( $this->px->req()->get_param('ziptype').''=>' checked="checked"' );
			foreach( array_keys( $is_zip ) as $type ){
				$RTN .= '	<li><label><input type="radio" name="ziptype" value="'.htmlspecialchars( strtolower($type) ).'"'.$c[$type].' /> '.strtoupper($type).'形式</label></li>'."\n";
			}
			$RTN .= '</ul>'."\n";
			if( strlen( $error['ziptype'] ) ){
				$RTN .= '<div class="error">'.$error['ziptype'].'</div>'."\n";
			}
		}else{
			#	圧縮解凍系機能が利用できなかったら
			$RTN .= '<p>'."\n";
			$RTN .= '	<span class="error">圧縮機能がセットアップされていません</span>。<code>$conf->path_commands[\'tar\']</code>に、tarコマンドのパスを設定するか、PHPにZIPサポートをインストールしてください。。<br />'."\n";
			$RTN .= '</p>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="確認する" /></p>'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="confirm" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href(':') ).'" method="post">'."\n";
		// $RTN .= '	'.$this->mk_form_defvalues( $this->site->get_parent( $this->req->p() ) )."\n";
		$RTN .= '	<p class="center"><input type="submit" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	/**
	 * プロジェクトをエクスポート：確認
	 */
	private function page_export_confirm(){
		$project_model = &$this->pcconf->factory_model_project();

		$RTN = '';
		$HIDDEN = '';

		$RTN .= '<p>'."\n";
		$RTN .= '	入力内容に間違いがないことをご確認ください。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<p>'."\n";
		$RTN .= '	よろしければ、「エクスポートする」ボタンをクリックしてください。<br />'."\n";
		$RTN .= '</p>'."\n";

		$RTN .= '<table style="width:100%;" class="form_elements">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>対象プロジェクト</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<ul class="form_elements-list">'."\n";
		$project_list = $project_model->get_project_list();
		foreach( $project_list as $Line ){
			$in_project = $this->px->req()->get_param('project');
			if( !$in_project[$Line['id']] ){ continue; }
			$RTN .= '				<li>'.htmlspecialchars($Line['name']).' ('.htmlspecialchars($Line['id']).')</li>'."\n";
			$HIDDEN .= '<input type="hidden" name="project['.htmlspecialchars($Line['id']).']" value="1" />';
		}
		$RTN .= '			</ul>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>圧縮形式</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( strtoupper( $this->px->req()->get_param('ziptype') ) ).' 形式</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="ziptype" value="'.htmlspecialchars( $this->px->req()->get_param('ziptype') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<div class="unit center">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.''."\n";
		$RTN .= '	<input type="submit" value="エクスポートする" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="input" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.''."\n";
		$RTN .= '	<input type="submit" value="訂正する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href(':') ).'" method="post">'."\n";
		// $RTN .= '	'.$this->mk_form_defvalues( $this->site->get_parent( $this->req->p() ) )."\n";
		$RTN .= '	<p class="center"><input type="submit" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	/**
	 * プロジェクトをエクスポート：チェック
	 */
	private function check_export_check(){
		$RTN = array();
		if( !count( $this->px->req()->get_param('project') ) ){
			$RTN['project'] = '対象プロジェクトを選択してください。';
		}
		if( !strlen( $this->px->req()->get_param('ziptype') ) ){
			$RTN['ziptype'] = '圧縮形式を選択してください。';
		}else{
			$is_zip = array();
			if( class_exists( 'ZipArchive' ) ){
				$is_zip['zip'] = true;
			}
			if( strlen( $this->pcconf->get_path_command('tar') ) ){
				$is_zip['tgz'] = true;
			}
			if( !count( $is_zip ) ){
				$RTN['ziptype'] = '圧縮形式が選択できません。システムにインストールしてください。';
			}elseif( !$is_zip[$this->px->req()->get_param('ziptype')] ){
				$RTN['ziptype'] = '対応していない圧縮形式です。';
			}
		}
		return	$RTN;
	}
	/**
	 * プロジェクトをエクスポート：実行
	 */
	private function execute_export_execute(){
		// if( !$this->user->save_t_lastaction() ){
		// 	#	2重書き込み防止
		// 	return	$this->px->redirect( $this->href().'&mode=thanks' );
		// }

		$className = $this->px->load_px_plugin_class('/asazuke/resources/io.php');
		if( !$className ){
			return '<p class="error">I/Oモジュールをロードできません。</p>';
		}
		$io = new $className( $this->px, $this->pcconf );

		if( !$this->px->dbh()->lock() ){
			return	'<p class="error">アプリケーションがロックされています。しばらく時間をおいてから、もう一度操作してみてください。</p>';
		}

		$path_export_archive = $io->mk_export_file( $this->px->req()->get_param('ziptype') , array( 'project'=>$this->px->req()->get_param('project') ) );
		if( $path_export_archive === false ){
			$this->px->dbh()->unlock();
			$this->px->error()->error_log( 'アーカイブの作成に失敗しました。' , __FILE__ , __LINE__ );
			return	'<p class="error">アーカイブの作成に失敗しました。</p>';
		}

		$this->px->dbh()->unlock();

		$result = $this->px->flush_file( $path_export_archive , array( 'filename'=>basename($path_export_archive) , 'delete'=>true ) );

		return	$this->px->redirect( $this->href().'&mode=thanks' );
	}
	/**
	 * プロジェクトをエクスポート：完了
	 */
	private function page_export_thanks(){
		$RTN = '';
		$RTN .= '<p>プロジェクトをエクスポート処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->href(':') ).'" method="post">'."\n";
		$RTN .= '	<p><input type="submit" value="戻る" /></p>'."\n";
		// $RTN .= '	'.$this->mk_form_defvalues( $this->site->get_parent( $this->req->p() ) )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}


	/**
	 * プロジェクトの削除
	 */
	private function start_delete_proj(){
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_delete_proj_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'execute' ){
			return	$this->execute_delete_proj_execute();
		}
		return	$this->page_delete_proj_confirm();
	}
	/**
	 * プロジェクトの削除：確認
	 */
	private function page_delete_proj_confirm(){
		$RTN = ''."\n";
		$HIDDEN = ''."\n";

		$RTN .= '<p>プロジェクトを削除します。</p>'."\n";
		$RTN .= '<p>よろしいですか？</p>'."\n";

		$RTN .= '<div class="unit center">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.''."\n";
		$RTN .= '	<input type="submit" value="削除する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href( ':detail.'.$this->cmd[1] ) ).'" method="post">'."\n";
		// $RTN .= '	'.$this->mk_form_defvalues( ':detail.'.$this->cmd[1] )."\n";
		$RTN .= '	<div class="center"><input type="submit" value="キャンセル" /></div>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	/**
	 * プロジェクトの削除：実行
	 */
	private function execute_delete_proj_execute(){
		// if( !$this->user->save_t_lastaction() ){
		// 	#	2重書き込み防止
		// 	return	$this->px->redirect( $this->href().'&mode=thanks' );
		// }

		if( !strlen( $this->cmd[1] ) ){
			return	'<p class="error">プロジェクトが選択されていません。</p>';
		}

		$project_model = &$this->pcconf->factory_model_project();
		$project_model->load_project( $this->cmd[1] );

		$result = $project_model->destroy_project();

		if( !$result ){
			return	'<p class="error">プロジェクトの削除に失敗しました。</p>';
		}

		return	$this->px->redirect( $this->href().'&mode=thanks' );
	}
	/**
	 * プロジェクトの削除：完了
	 */
	private function page_delete_proj_thanks(){
		$RTN = ''."\n";
		$RTN .= '<p>プロジェクトの削除処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->href( ':' ) ).'" method="post">'."\n";
		$RTN .= '	<p><input type="submit" value="戻る" /></p>'."\n";
		// $RTN .= '	'.$this->mk_form_defvalues( ':' )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}




	###################################################################################################################


	/**
	 * 新規プログラム作成/編集
	 */
	private function start_edit_program(){
		if( $this->cmd[0] == 'edit_program' ){
			if( !strlen( $this->cmd[1] ) || !strlen( $this->cmd[2] ) ){
				return $this->theme->errorend('編集対象のプログラムが指定されていません。');
			}
		}
		$error = $this->check_edit_program_check();
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_edit_program_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'confirm' && !count( $error ) ){
			return	$this->page_edit_program_confirm();
		}elseif( $this->px->req()->get_param('mode') == 'execute' && !count( $error ) ){
			return	$this->execute_edit_program_execute();
		}elseif( !strlen( $this->px->req()->get_param('mode') ) ){
			$error = array();
			if( $this->cmd[0] == 'edit_program' ){
				$project_model = &$this->pcconf->factory_model_project();
				$project_model->load_project( $this->cmd[1] );
				$program_model = &$project_model->factory_program( $this->cmd[2] );
				$this->px->req()->set_param( 'program_name' , $program_model->get_program_name() );
				$this->px->req()->set_param( 'program_param' , $program_model->get_program_param() );
				$this->px->req()->set_param( 'program_type' , $program_model->get_program_type() );
				$this->px->req()->set_param( 'program_useragent' , $program_model->get_program_useragent() );
				$this->px->req()->set_param( 'path_copyto' , $program_model->get_path_copyto() );//10:54 2009/08/27 追加
				$this->px->req()->set_param( 'copyto_apply_deletedfile_flg' , $program_model->get_copyto_apply_deletedfile_flg() );//10:54 2009/08/27 追加

				$urllist_scope = $program_model->get_urllist_scope();
				$str_urllist = '';
				foreach( $urllist_scope as $url ){
					$str_urllist .= $url."\n";
				}
				$this->px->req()->set_param( 'urllist_scope' , $str_urllist );

				$urllist_nodownload = $program_model->get_urllist_nodownload();
				$str_urllist = '';
				foreach( $urllist_nodownload as $url ){
					$str_urllist .= $url."\n";
				}
				$this->px->req()->set_param( 'urllist_nodownload' , $str_urllist );
			}else{
				#	デフォルト値を設定
				$this->px->req()->set_param( 'program_name' , 'New Program' );
				$this->px->req()->set_param( 'program_useragent' , 'PicklesCrawler' );
			}
		}
		return	$this->page_edit_program_input( $error );
	}
	/**
	 * 新規プログラム作成/編集：入力
	 */
	private function page_edit_program_input( $error ){
		$RTN = ''."\n";

		$RTN .= '<p>プログラムの設定情報を入力して、「確認する」をクリックしてください。<span class="form_elements-must">必須</span>印がついている項目は必ず入力してください。</p>'."\n";

		$RTN .= '<form action="'.htmlspecialchars( $this->href() ).'" method="post">'."\n";
		$RTN .= '<table style="width:100%;" class="form_elements">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>プログラム名 <span class="form_elements-must">必須</span></div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div><input type="text" name="program_name" value="'.htmlspecialchars( $this->px->req()->get_param('program_name') ).'" /></div>'."\n";
		if( strlen( $error['program_name'] ) ){
			$RTN .= '			<div class="error">'.$error['program_name'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>常に送信するパラメータ</div></th>'."\n";//PicklesCrawler 0.3.0 追加
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div><input type="text" name="program_param" value="'.htmlspecialchars( $this->px->req()->get_param('program_param') ).'" /></div>'."\n";
		if( strlen( $error['program_param'] ) ){
			$RTN .= '			<div class="error">'.$error['program_param'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>HTTP_USER_AGENT</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div><input type="text" name="program_useragent" value="'.htmlspecialchars( $this->px->req()->get_param('program_useragent') ).'" /></div>'."\n";
		if( strlen( $error['program_useragent'] ) ){
			$RTN .= '			<div class="error">'.$error['program_useragent'].'</div>'."\n";
		}
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>対象範囲とするURLリスト</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div><textarea name="urllist_scope" rows="7">'.htmlspecialchars( $this->px->req()->get_param('urllist_scope') ).'</textarea></div>'."\n";
		if( strlen( $error['urllist_scope'] ) ){
			$RTN .= '			<div class="error">'.$error['urllist_scope'].'</div>'."\n";
		}
		$RTN .= '			<ul class="form_elements-notes">'."\n";
		$RTN .= '				<li>※プロトコル部(http://またはhttps://)から始まる完全なURLで指定してください。</li>'."\n";
		$RTN .= '				<li>※改行区切りで複数登録することができます。</li>'."\n";
		$RTN .= '				<li>※アスタリスク(*)記号でワイルドカードを表現できます。</li>'."\n";
		$RTN .= '			</ul>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>ダウンロードしないURLリスト</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div><textarea name="urllist_nodownload" rows="7">'.htmlspecialchars( $this->px->req()->get_param('urllist_nodownload') ).'</textarea></div>'."\n";
		if( strlen( $error['urllist_nodownload'] ) ){
			$RTN .= '			<div class="error">'.$error['urllist_nodownload'].'</div>'."\n";
		}
		$RTN .= '			<ul class="form_elements-notes">'."\n";
		$RTN .= '				<li>※プロトコル部(http://またはhttps://)から始まる完全なURLで指定してください。</li>'."\n";
		$RTN .= '				<li>※改行区切りで複数登録することができます。</li>'."\n";
		$RTN .= '				<li>※アスタリスク(*)記号でワイルドカードを表現できます。</li>'."\n";
		$RTN .= '			</ul>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>複製先パス</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div><input type="text" name="path_copyto" value="'.htmlspecialchars( $this->px->req()->get_param('path_copyto') ).'" /></div>'."\n";
		if( strlen( $error['path_copyto'] ) ){
			$RTN .= '			<div class="error">'.$error['path_copyto'].'</div>'."\n";
		}
		$c = array( '1'=>' checked="checked"' );
		$RTN .= '			<div><label><input type="checkbox" name="copyto_apply_deletedfile_flg" value="1"'.htmlspecialchars( $c[$this->px->req()->get_param('copyto_apply_deletedfile_flg')] ).' /> 削除されたファイルを反映する</label></div>'."\n";
		if( strlen( $error['copyto_apply_deletedfile_flg'] ) ){
			$RTN .= '			<div class="error">'.$error['copyto_apply_deletedfile_flg'].'</div>'."\n";
		}
		$RTN .= '			<ul class="form_elements-notes">'."\n";
		$RTN .= '				<li>※プロジェクトに設定された「複製先パス」を上書きします。ここで空白を設定すると、プロジェクトの「複製先パス」が採用されます。</li>'."\n";
		$RTN .= '				<li>※複製先パスは、既に存在するパスである必要があります。</li>'."\n";
		$RTN .= '			</ul>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="確認する" /></p>'."\n";
		$RTN .= '	<input type="hidden" name="program_type" value="snapshot" />'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="confirm" />'."\n";
		$RTN .= '	'.''."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	/**
	 * 新規プログラム作成/編集：確認
	 */
	private function page_edit_program_confirm(){
		$RTN = ''."\n";
		$HIDDEN = ''."\n";

		$RTN .= '<p>入力したプログラムの設定情報を確認してください。</p>'."\n";

		$RTN .= '<table style="width:100%;" class="form_elements">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>プログラム名</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('program_name') ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="program_name" value="'.htmlspecialchars( $this->px->req()->get_param('program_name') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>常に送信するパラメータ</div></th>'."\n";//PicklesCrawler 0.3.0
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('program_param') ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="program_param" value="'.htmlspecialchars( $this->px->req()->get_param('program_param') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$HIDDEN .= '<input type="hidden" name="program_type" value="'.htmlspecialchars( $this->px->req()->get_param('program_type') ).'" />';
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>HTTP_USER_AGENT</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('program_useragent') ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="program_useragent" value="'.htmlspecialchars( $this->px->req()->get_param('program_useragent') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>対象範囲とするURLリスト</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$inputvalueList = preg_split( '/\r\n|\r|\n/' , $this->px->req()->get_param('urllist_scope') );
		foreach( $inputvalueList as $key=>$val ){
			$val = trim($val);
			if( !strlen( $val ) ){
				unset( $inputvalueList[$key] ); continue;
			}
		}
		if( count( $inputvalueList ) ){
			$RTN .= '			<ul>'."\n";
			foreach( $inputvalueList as $val ){
				$RTN .= '				<li>'.htmlspecialchars( $val ).'</li>'."\n";
			}
			$RTN .= '			</ul>'."\n";
		}else{
			$RTN .= '			<div class="ttrs">指定されていません。</div>'."\n";
		}
		$HIDDEN .= '<input type="hidden" name="urllist_scope" value="'.htmlspecialchars( $this->px->req()->get_param('urllist_scope') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>ダウンロードしないURLリスト</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		$inputvalueList = preg_split( '/\r\n|\r|\n/' , $this->px->req()->get_param('urllist_nodownload') );
		foreach( $inputvalueList as $key=>$val ){
			$val = trim($val);
			if( !strlen( $val ) ){
				unset( $inputvalueList[$key] ); continue;
			}
		}
		if( count( $inputvalueList ) ){
			$RTN .= '			<ul>'."\n";
			foreach( $inputvalueList as $val ){
				$RTN .= '				<li>'.htmlspecialchars( $val ).'</li>'."\n";
			}
			$RTN .= '			</ul>'."\n";
		}else{
			$RTN .= '			<div class="ttrs">指定されていません。</div>'."\n";
		}
		$HIDDEN .= '<input type="hidden" name="urllist_nodownload" value="'.htmlspecialchars( $this->px->req()->get_param('urllist_nodownload') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;"><div>複製先パス</div></th>'."\n";
		$RTN .= '		<td style="width:70%;">'."\n";
		if( strlen( $this->px->req()->get_param('path_copyto') ) ){
			$RTN .= '			<div>'.htmlspecialchars( $this->px->req()->get_param('path_copyto') ).'</div>'."\n";
		}else{
			$RTN .= '			<div>---</div>'."\n";
		}
		$HIDDEN .= '<input type="hidden" name="path_copyto" value="'.htmlspecialchars( $this->px->req()->get_param('path_copyto') ).'" />';
		$c = array( '1'=>'する' , '0'=>'しない' );
		$RTN .= '			<div>削除されたファイルを反映'.htmlspecialchars( $c[intval($this->px->req()->get_param('copyto_apply_deletedfile_flg'))] ).'</div>'."\n";
		$HIDDEN .= '<input type="hidden" name="copyto_apply_deletedfile_flg" value="'.htmlspecialchars( $this->px->req()->get_param('copyto_apply_deletedfile_flg') ).'" />';
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";

		$RTN .= '<div class="unit center">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.''."\n";
		$RTN .= '	<input type="submit" value="保存する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="input" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.''."\n";
		$RTN .= '	<input type="submit" value="訂正する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href( ':detail.'.$this->cmd[1] ) ).'" method="post">'."\n";
		// $RTN .= '	'.$this->mk_form_defvalues( ':detail.'.$this->cmd[1] )."\n";
		$RTN .= '	<p class="center"><input type="submit" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	/**
	 * 新規プログラム作成/編集：チェック
	 */
	private function check_edit_program_check(){
		$RTN = array();
		if( !strlen( $this->px->req()->get_param('program_name') ) ){
			$RTN['program_name'] = 'プログラム名は必須項目です。';
		}elseif( preg_match( '/\r\n|\r|\n/' , $this->px->req()->get_param('program_name') ) ){
			$RTN['program_name'] = 'プログラム名に改行を含むことはできません。';
		}
		if( preg_match( '/\r\n|\r|\n/' , $this->px->req()->get_param('program_useragent') ) ){
			$RTN['program_useragent'] = 'ユーザエージェントに改行を含むことはできません。';
		}
		if( !strlen( $this->px->req()->get_param('program_type') ) ){
			$RTN['program_type'] = 'タイプを選択してください。';
		}elseif( preg_match( '/\r\n|\r|\n/' , $this->px->req()->get_param('program_type') ) ){
			$RTN['program_type'] = 'タイプに改行を含むことはできません。';
		}
		if( strlen( $this->px->req()->get_param('path_copyto') ) ){
			if( !is_dir( $this->px->req()->get_param('path_copyto') ) ){
				$RTN['path_copyto'] = '複製先パスには、ディレクトリが存在している必要があります。';
			}
		}
		return	$RTN;
	}
	/**
	 * 新規プログラム作成/編集：実行
	 */
	private function execute_edit_program_execute(){
		// if( !$this->user->save_t_lastaction() ){
		// 	#	2重書き込み防止
		// 	$this->px->redirect( $this->href().'&mode=thanks' );
		// }

		$project_model = &$this->pcconf->factory_model_project();
		$project_model->load_project( $this->cmd[1] );

		if( $this->cmd[0] == 'edit_program' ){
			$program_model = &$project_model->factory_program( $this->cmd[2] );
		}else{
			$program_model = &$project_model->factory_program();
		}

		$program_model->set_program_name( $this->px->req()->get_param('program_name') );
		$program_model->set_program_param( $this->px->req()->get_param('program_param') );//PicklesCrawler 0.3.0
		$program_model->set_program_type( $this->px->req()->get_param('program_type') );
		$program_model->set_program_useragent( $this->px->req()->get_param('program_useragent') );
		$program_model->set_path_copyto( $this->px->req()->get_param('path_copyto') );//10:56 2009/08/27 PicklesCrawler 0.3.3 追加
		$program_model->set_copyto_apply_deletedfile_flg( $this->px->req()->get_param('copyto_apply_deletedfile_flg') );//10:56 2009/08/27 PicklesCrawler 0.3.3 追加
		$program_model->set_urllist_scope( $this->px->req()->get_param('urllist_scope') );
		$program_model->set_urllist_nodownload( $this->px->req()->get_param('urllist_nodownload') );


		#	出来上がったプログラムを保存
		if( !$program_model->save_program() ){
			return	'<p class="error">プログラムの保存に失敗しました。</p>';
		}

		return	$this->px->redirect( $this->href().'&mode=thanks&program_id='.urlencode($program_model->get_program_id()) );
	}
	/**
	 * 新規プログラム作成/編集：完了
	 */
	private function page_edit_program_thanks(){
		$RTN = ''."\n";
		if( $this->cmd[0] == 'edit_program' ){
			$RTN .= '<p>プログラム '.htmlspecialchars( $this->px->req()->get_param('program_id') ).' の編集処理を保存しました。</p>';
		}else{
			$RTN .= '<p>新規プログラム '.htmlspecialchars( $this->px->req()->get_param('program_id') ).' を作成しました。</p>';
		}
		$RTN .= '<div class="unit">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href( ':execute_program.'.$this->cmd[1].'.'.urlencode( $this->px->req()->get_param('program_id') ) ) ).'" method="post">'."\n";
		$RTN .= '	<div class="inline">'."\n";
		$RTN .= '		<input type="submit" value="実行する" />'."\n";
		// $RTN .= '		'.$this->mk_form_defvalues( ':execute_program.'.$this->cmd[1].'.'.urlencode( $this->px->req()->get_param('program_id') ) )."\n";
		$RTN .= '	</div>'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href( ':detail.'.$this->cmd[1] ) ).'" method="post">'."\n";
		$RTN .= '	<div class="inline">'."\n";
		$RTN .= '		<input type="submit" value="戻る" />'."\n";
		// $RTN .= '		'.$this->mk_form_defvalues( ':detail.'.$this->cmd[1] )."\n";
		$RTN .= '	</div>'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		return	$RTN;
	}



	###################################################################################################################

	/**
	 * プログラムを実行
	 */
	private function start_execute_program(){
		if( $this->px->req()->get_param('mode') == 'download' ){
			#	ダウンロードする場合
			return	$this->download_program_content();
		}

		$project_model = &$this->pcconf->factory_model_project();
		$project_model->load_project( $this->cmd[1] );
		$program_model = &$project_model->factory_program( $this->cmd[2] );

		$exec_page_id = ':run.'.$this->cmd[1].'.'.$this->cmd[2];

		$RTN = ''."\n";
		$RTN .= '<div class="unit cols">'."\n";
		$RTN .= '	<div class="cols-col cols-2of3"><div class="cols-pad">'."\n";

		$RTN .= '<p>'."\n";
		$RTN .= '	プロジェクト『<strong>'.htmlspecialchars( $project_model->get_project_name() ).'</strong>('.htmlspecialchars( $project_model->get_project_id() ).')』のプログラム『<strong>'.htmlspecialchars( $program_model->get_program_name() ).'</strong>('.htmlspecialchars( $program_model->get_program_id() ).')』を実行します。設定を確認してください。<br />'."\n";
		$RTN .= '</p>'."\n";

		$RTN .= $this->mk_hx('このプログラムの情報')."\n";
		$RTN .= '<table class="def" style="width:100%;">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>プロジェクト名 (プロジェクトID)</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $project_model->get_project_name() ).' ('.htmlspecialchars( $project_model->get_project_id() ).')</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>プログラム名 (プログラムID)</th>'."\n";
		$RTN .= '		<td>'.htmlspecialchars( $program_model->get_program_name() ).' ('.htmlspecialchars( $program_model->get_program_id() ).')</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>常に送信するパラメータ</th>'."\n";
		$RTN .= '		<td><div style="overflow:hidden;">'.htmlspecialchars( $program_model->get_program_param() ).'</div></td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>HTTP_USER_AGENT</th>'."\n";
		$RTN .= '		<td><div style="overflow:hidden;">'.htmlspecialchars( $program_model->get_program_useragent() ).'</div></td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>対象範囲とするURL</th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div style="overflow:hidden;">'."\n";

		$urllist_scope = $program_model->get_urllist_scope();
		if( count( $urllist_scope ) ){
			$RTN .= '<ul>'."\n";
			foreach( $urllist_scope as $url ){
				$RTN .= '<li>'.htmlspecialchars($url).'</li>'."\n";
			}
			$RTN .= '</ul>'."\n";
		}else{
			$RTN .= '<div>全てのURLが対象です。</div>'."\n";
		}
		$RTN .= '			</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th>ダウンロード対象外のURL</th>'."\n";
		$RTN .= '		<td>'."\n";
		$RTN .= '			<div style="overflow:hidden;">'."\n";
		$urllist_nodownload = $program_model->get_urllist_nodownload();
		if( count( $urllist_nodownload ) ){
			$RTN .= '<ul>'."\n";
			foreach( $urllist_nodownload as $url ){
				$RTN .= '<li>'.htmlspecialchars($url).'</li>'."\n";
			}
			$RTN .= '</ul>'."\n";
		}else{
			$RTN .= '<div>ダウンロード対象外に指定されたURLはありません。</div>'."\n";
		}
		$RTN .= '			</div>'."\n";
		$RTN .= '		</td>'."\n";
		$RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th>複製先パス</th>'."\n";
		// $RTN .= '		<td>'."\n";
		// $path_copyto = $project_model->get_path_copyto();
		// if( strlen( $program_model->get_path_copyto() ) ){
		// 	$path_copyto = $program_model->get_path_copyto();
		// }
		// if( strlen( $path_copyto ) ){
		// 	$RTN .= '			<div>'.htmlspecialchars( $path_copyto ).'</div>'."\n";
		// 	if( !is_dir( $path_copyto ) ){
		// 		$RTN .= '			<div class="error">ディレクトリが存在しません。</div>'."\n";
		// 	}elseif( !is_writable( $path_copyto ) ){
		// 		$RTN .= '			<div class="error">ディレクトリに書き込みできません。</div>'."\n";
		// 	}elseif( !$this->dbh->check_rootdir( $path_copyto ) ){
		// 		$RTN .= '			<div class="error">ディレクトリが管理外のパスです。</div>'."\n";
		// 	}
		// }else{
		// 	$RTN .= '			<div>---</div>'."\n";
		// }
		// if( $program_model->get_copyto_apply_deletedfile_flg() ){
		// 	$RTN .= '			<div>削除されたファイルを反映する</div>'."\n";
		// }else{
		// 	$RTN .= '			<div>削除されたファイルを反映しない</div>'."\n";
		// }
		// $RTN .= '		</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";
		$RTN .= '<p>'.$this->mk_link(':edit_program.'.$this->cmd[1].'.'.$this->cmd[2],array('label'=>'このプログラムを編集する','style'=>'inside')).'</p>'."\n";

		if( $this->px->dbh()->is_unix() ){
			#--------------------------------------
			#	UNIXの場合→コマンドラインでの実行方法を案内。
			$RTN .= $this->mk_hx('このプログラムの実行')."\n";
			$RTN .= '<p>'."\n";
			$RTN .= '	この操作は、次のコマンドラインからも実行することができます。<br />'."\n";
			$RTN .= '</p>'."\n";
			$RTN .= '<blockquote><div>';
			$RTN .= htmlspecialchars( ''.escapeshellcmd( $this->pcconf->get_path_command('php') ).' '.escapeshellarg( realpath( './_px_execute.php' ) ).' '.escapeshellarg( 'PX=plugins.asazuke.run.'.$this->cmd[1].'.'.$this->cmd[2].'&output_encoding='.urlencode('UTF-8').'' ) );
			$RTN .= '</div></blockquote>'."\n";

			$RTN .= '<p>'."\n";
			$RTN .= '	このコマンドを、ウェブから起動するには、次の「実行する」ボタンをクリックします。<br />'."\n";
			$RTN .= '</p>'."\n";
		}else{
			#--------------------------------------
			#	Windowsの場合→コマンドラインで実行できない・・・。
			$RTN .= $this->mk_hx('このプログラムの実行')."\n";
			$RTN .= '<p>'."\n";
			$RTN .= '	プログラムを実行するには、次の「実行する」ボタンをクリックしてください。<br />'."\n";
			$RTN .= '</p>'."\n";
		}

		$RTN .= '<form action="'.htmlspecialchars( $this->href( $exec_page_id ) ).'" method="post" target="_blank">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="実行する" /></p>'."\n";
		$RTN .= '</form>'."\n";


		$RTN .= '	</div></div>'."\n";
		$RTN .= '	<div class="cols-col cols-1of3 cols-last"><div class="cols-pad">'."\n";

		$RTN .= $this->mk_hx('書き出したデータのダウンロード')."\n";
		$is_zip = array();
		if( class_exists( 'ZipArchive' ) ){
			$is_zip['zip'] = true;
		}
		if( strlen( $this->pcconf->get_path_command('tar') ) ){
			$is_zip['tgz'] = true;
		}
		if( count( $is_zip ) ){
			#	tarコマンドが使えたら(UNIXのみ)
			$RTN .= '<p>'."\n";
			$RTN .= '	書き出したデータを';
			$RTN .= implode( ', ' , array_keys( $is_zip ) );
			$RTN .= '形式でダウンロードすることができます。';
			$RTN .= '<br />'."\n";
			$RTN .= '</p>'."\n";
			$RTN .= '<ul class="none">'."\n";
			foreach( array_keys( $is_zip ) as $type ){
				$RTN .= '	<li>'.$this->mk_link( ':'.implode('.', $this->cmd).'&mode=download&ext='.strtolower($type) , array('label'=>strtoupper($type).'形式でダウンロード','active'=>false,'style'=>'inside') ).'</li>'."\n";
			}
			$RTN .= '</ul>'."\n";
		}else{
			#	圧縮解凍系機能が利用できなかったら
			$RTN .= '<p>'."\n";
			$RTN .= '	<span class="error">tarコマンドのパスがセットされていません</span>。<code>$conf->path_commands[\'tar\']</code>に、tarコマンドのパスを設定してください。<br />'."\n";
			$RTN .= '</p>'."\n";
		}

		$RTN .= $this->mk_hx('書き出したデータの削除')."\n";
		$RTN .= '<p>'."\n";
		$RTN .= '	書き出したデータを削除します。<br />'."\n";
		$RTN .= '</p>'."\n";
		$RTN .= '<ul class="none">'."\n";
		$RTN .= '	<li>'.$this->mk_link( ':delete_program_content.'.$this->cmd[1].'.'.$this->cmd[2] , array('label'=>'削除する','active'=>false,'style'=>'inside') ).'</li>'."\n";
		$RTN .= '</ul>'."\n";

		$RTN .= '	</div></div>'."\n";
		$RTN .= '</div>'."\n";

		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href( ':detail.'.$this->cmd[1] ) ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
		$RTN .= '	'.$this->mk_form_defvalues( ':detail.'.$this->cmd[1] )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}//start_execute_program()


	/**
	 * プログラムが書き出したコンテンツのダウンロード
	 */
	private function download_program_content(){
		$download_content_path = $this->pcconf->get_program_home_dir( $this->cmd[1] , $this->cmd[2] ).'/dl';
		$download_zipto_path = dirname($download_content_path).'/tmp_download_content';
		if( !is_dir( $download_content_path ) ){
			return	'<p class="error">ディレクトリが存在しません。</p>';
		}

		if( $this->px->req()->get_param('ext') == 'tgz' && strlen( $this->pcconf->get_path_command('tar') ) ){
			#	tarコマンドが使えたら(UNIXのみ)
			$className = $this->px->load_px_plugin_class( '/asazuke/resources/tgz.php' );
			if( !$className ){
				$this->px->error()->error_log( 'tgzライブラリのロードに失敗しました。' , __FILE__ , __LINE__ );
				return	'<p class="error">tgzライブラリのロードに失敗しました。</p>';
			}
			$obj_tgz = new $className( $this->px , $this->pcconf->get_path_command('tar') );

			if( !$obj_tgz->zip( $download_content_path , $download_zipto_path.'.tgz' ) ){
				return	'<p class="error">圧縮に失敗しました。</p>';
			}

			if( !is_file( $download_zipto_path.'.tgz' ) ){
				return	'<p class="error">圧縮されたアーカイブファイルは現在は存在しません。</p>';
			}

			$dl_filename = $this->cmd[1].'_'.$this->cmd[2].'.tgz';
			if( $this->pcconf->get_value('dl_datetime_in_filename') ){
				$CONTENT = $this->px->dbh()->file_get_contents( $download_content_path.'/__LOGS__/datetime.txt' );
				list( $start_datetime , $end_datetime ) = explode(' --- ',$CONTENT);
				if( !strlen( $end_datetime ) ){
					$end_datetime = date('Y-m-d H:i:s');
				}
				$dl_filename = $this->cmd[1].'_'.date('Ymd_Hi',$this->px->dbh()->datetime2int($end_datetime)).'_'.$this->cmd[2].'.tgz';
			}
			$download_zipto_path = $download_zipto_path.'.tgz';

		}elseif( $this->px->req()->get_param('ext') == 'zip' && class_exists( 'ZipArchive' ) ){
			#	ZIP関数が有効だったら
			$className = $this->px->load_px_plugin_class( '/asazuke/resources/zip.php' );
			if( !$className ){
				$this->px->error()->error_log( 'zipライブラリのロードに失敗しました。' , __FILE__ , __LINE__ );
				return	'<p class="error">zipライブラリのロードに失敗しました。</p>';
			}
			$obj_zip = new $className( $this->px );
			if( !$obj_zip->zip( $download_content_path , $download_zipto_path.'.zip' ) ){
				return	'<p class="error">圧縮に失敗しました。</p>';
			}

			if( !is_file( $download_zipto_path.'.zip' ) ){
				return	'<p class="error">圧縮されたアーカイブファイルは現在は存在しません。</p>';
			}

			$dl_filename = $this->cmd[1].'_'.$this->cmd[2].'.zip';
			if( $this->pcconf->get_value('dl_datetime_in_filename') ){
				$CONTENT = $this->px->dbh()->file_get_contents( $download_content_path.'/__LOGS__/datetime.txt' );
				list( $start_datetime , $end_datetime ) = explode(' --- ',$CONTENT);
				if( !strlen( $end_datetime ) ){
					$end_datetime = date('Y-m-d H:i:s');
				}
				$dl_filename = $this->cmd[1].'_'.date('Ymd_Hi',$this->px->dbh()->datetime2int($end_datetime)).'_'.$this->cmd[2].'.zip';
			}
			$download_zipto_path = $download_zipto_path.'.zip';

		}

		$result = $this->px->flush_file( $download_zipto_path , array( 'filename'=>$dl_filename , 'delete'=>true ) );
		if( $result === false ){
			return	'<p class="error">作成されたアーカイブのダウンロードに失敗しました。</p>';
		}
		return	$result;
	}//download_program_content()


	###################################################################################################################

	/**
	 * プログラムが書き出したコンテンツの削除
	 */
	private function start_delete_program_content(){
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_delete_program_content_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'execute' ){
			return	$this->execute_delete_program_content_execute();
		}
		return	$this->page_delete_program_content_confirm();
	}
	/**
	 * プログラムが書き出したコンテンツの削除：確認
	 */
	private function page_delete_program_content_confirm(){
		$RTN = ''."\n";
		$HIDDEN = ''."\n";

		$RTN .= '<p>プログラムが書き出したコンテンツを削除します。</p>'."\n";
		$RTN .= '<p>よろしいですか？</p>'."\n";

		$RTN .= '<div class="unit center">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.''."\n";
		$RTN .= '	<input type="submit" value="削除する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href( ':execute_program.'.$this->cmd[1].'.'.$this->cmd[2] ) ).'" method="post">'."\n";
		// $RTN .= '	'.$this->mk_form_defvalues( ':execute_program.'.$this->cmd[1].'.'.$this->cmd[2] )."\n";
		$RTN .= '	<p class="center"><input type="submit" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	/**
	 * プログラムが書き出したコンテンツの削除：実行
	 */
	private function execute_delete_program_content_execute(){
		// if( !$this->user->save_t_lastaction() ){
		// 	#	2重書き込み防止
		// 	return	$this->px->redirect( $this->href().'&mode=thanks' );
		// }

		if( !strlen( $this->cmd[1] ) ){
			return	'<p class="error">プロジェクトが選択されていません。</p>';
		}
		if( !strlen( $this->cmd[2] ) ){
			return	'<p class="error">プログラムが選択されていません。</p>';
		}


		$project_model = &$this->pcconf->factory_model_project();
		$project_model->load_project( $this->cmd[1] );
		$program_model = &$project_model->factory_program( $this->cmd[2] );
		$result = $program_model->delete_program_content();

		if( !$result ){
			return	'<p class="error">プログラムコンテンツの削除に失敗しました。</p>';
		}

		return	$this->px->redirect( $this->href().'&mode=thanks' );
	}
	/**
	 * プログラムが書き出したコンテンツの削除：完了
	 */
	private function page_delete_program_content_thanks(){
		$RTN = ''."\n";
		$RTN .= '<p>プログラムコンテンツの削除処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->href( ':execute_program.'.$this->cmd[1].'.'.$this->cmd[2] ) ).'" method="post">'."\n";
		$RTN .= '	<input type="submit" value="戻る" />'."\n";
		// $RTN .= '	'.$this->mk_form_defvalues( ':execute_program.'.$this->cmd[1].'.'.$this->cmd[2] )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}






	###################################################################################################################

	/**
	 * プログラムの削除
	 */
	private function start_delete_program(){
		if( $this->px->req()->get_param('mode') == 'thanks' ){
			return	$this->page_delete_program_thanks();
		}elseif( $this->px->req()->get_param('mode') == 'execute' ){
			return	$this->execute_delete_program_execute();
		}
		return	$this->page_delete_program_confirm();
	}
	/**
	 * プログラムの削除：確認
	 */
	private function page_delete_program_confirm(){
		$RTN = ''."\n";
		$HIDDEN = ''."\n";

		$RTN .= '<p>プログラムを削除します。</p>'."\n";
		$RTN .= '<p>よろしいですか？</p>'."\n";

		$RTN .= '<div class="unit center">'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href() ).'" method="post">'."\n";
		$RTN .= '	<input type="hidden" name="mode" value="execute" />'."\n";
		$RTN .= $HIDDEN;
		$RTN .= '	'.''."\n";
		$RTN .= '	<input type="submit" value="削除する" />'."\n";
		$RTN .= '</form>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '<hr />'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href( ':detail.'.$this->cmd[1] ) ).'" method="post">'."\n";
		// $RTN .= '	'.$this->mk_form_defvalues( ':detail.'.$this->cmd[1] )."\n";
		$RTN .= '	<p class="center"><input type="submit" value="キャンセル" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}
	/**
	 * プログラムの削除：実行
	 */
	private function execute_delete_program_execute(){
		// if( !$this->user->save_t_lastaction() ){
		// 	#	2重書き込み防止
		// 	return	$this->px->redirect( $this->href().'&mode=thanks' );
		// }

		if( !strlen( $this->cmd[1] ) ){
			return	'<p class="error">プロジェクトが選択されていません。</p>';
		}
		if( !strlen( $this->cmd[2] ) ){
			return	'<p class="error">プログラムが選択されていません。</p>';
		}


		$project_model = &$this->pcconf->factory_model_project();
		$project_model->load_project( $this->cmd[1] );
		$program_model = &$project_model->factory_program( $this->cmd[2] );
		$result = $program_model->destroy_program();

		if( !$result ){
			return	'<p class="error">プログラムの削除に失敗しました。</p>';
		}

		return	$this->px->redirect( $this->href().'&mode=thanks' );
	}
	/**
	 * プログラムの削除：完了
	 */
	private function page_delete_program_thanks(){
		$RTN = ''."\n";
		$RTN .= '<p>プログラムの削除処理を完了しました。</p>';
		$RTN .= '<form action="'.htmlspecialchars( $this->href( ':detail.'.$this->cmd[1] ) ).'" method="post">'."\n";
		$RTN .= '	<input type="submit" value="戻る" />'."\n";
		// $RTN .= '	'.$this->mk_form_defvalues( ':detail.'.$this->cmd[1] )."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}




	/**
	 * 設定項目の確認
	 */
	private function page_configcheck(){
		$RTN = ''."\n";
		$RTN .= '<div class="unit">'."\n";
		$RTN .= '<table class="def">'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">作業ディレクトリ</th>'."\n";
		$path = $this->pcconf->get_home_dir();
		if( is_dir( $path ) ){
			$path = realpath( $path );
		}
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $path ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">収集数の上限</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->pcconf->get_value('crawl_max_url_number') ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">phpのパス</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->pcconf->get_path_command('php') ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		$RTN .= '	<tr>'."\n";
		$RTN .= '		<th style="width:30%;">tarのパス</th>'."\n";
		$RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->pcconf->get_path_command('tar') ).'</td>'."\n";
		$RTN .= '	</tr>'."\n";
		// $RTN .= '	<tr>'."\n";
		// $RTN .= '		<th style="width:30%;">crawlctrl のページID</th>'."\n";
		// $RTN .= '		<td style="width:70%;">'.htmlspecialchars( $this->pcconf->pid['crawlctrl'] ).'</td>'."\n";
		// $RTN .= '	</tr>'."\n";
		$RTN .= '</table>'."\n";
		$RTN .= '</div>'."\n";
		$RTN .= '<form action="'.htmlspecialchars( $this->href(':') ).'" method="post">'."\n";
		$RTN .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
		$RTN .= '</form>'."\n";
		return	$RTN;
	}

}

?>