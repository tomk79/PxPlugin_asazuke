/**
 * PxPlugin "asazuke"
 * @author Tomoya Koyanagi (@tomk79)
 ************************************** */

asazuke は、既存のウェブサイトのデータを解析し、
Pickles Framework 1.x の形式に置換するスクレイピングツールです。
Pickles Framework 1.x 系のプラグインとして動作します。

asazuke は、ウェブサイトのクロールは行いません。
先に PicklesCrawlerプラグイン を使って巡回収集したデータを用意し、
そのデータを asazuke でスクレイピングする手順で行うと効率的です。

Pickles Framework については、
下記のウェブサイトを参照してください。
http://pickles.pxt.jp/

PicklesCrawlerプラグインについては、
下記にリポジトリが公開されています。
https://github.com/tomk79/PxPlugin_PicklesCrawler


■インストール方法

1. Pickles Framework をセットアップする。
2. ディレクトリ asazuke を、
   Pickles Framework の plugins ディレクトリにアップロードする。

■使い方

1. ブラウザで、PxCommand "?PX=plugins.asazuke" にアクセスする。

2. 「基本情報を編集する」ボタンを押し、設定を変更します。
    ・ドキュメントルートのパス: 
        取り込むもとのサイトデータのドキュメントルートを設定してください。
        ローカルの絶対パスで指定します。
    ・スタートページのパス:
        ドキュメントルートを / とした場合の、トップページのパスを設定してください。

3. 各種取り込みの規則設定を行ってください。
   ※0.1.0a1時点の仕様では、編集用の画面は用意されていませんので、
     手作業で設定ファイルを作成してください。
     ./_PX/_sys/ramdata/plugins/asazuke/proj/* に次のファイルを設置します。
       ・DOM変換ルール:
         dom_convert.csv
            A列: "name" => 設定名
            B列: "selector" => CSSセレクタ
            C列: "replace_to" => 置換後のHTMLソース
         ※上から順に全行適用
       ・除外共通リソース設定:
         ignore_common_resources.csv
            A列: "name" => 設定名
            B列: "path" => 場外するリソースのパス
         ※上から順に全行適用
       ・文字列置換ルール:
         replace_strings.csv
            A列: "name" => 設定名
            B列: "preg_pattern" => 正規表現パターン
            C列: "replace_to" => 置換後の文字列
         ※上から順に全行適用
       ・タイトルの置換ルール:
         replace_title.csv
            A列: "name" => 設定名
            B列: "preg_pattern" => 正規表現パターン
            C列: "replace_to" => 置換後の文字列
         ※上から順にはじめにマッチした行のみ適用
       ・パンくずエリアセレクタ:
         select_breadcrumb.csv
            A列: "name" => 設定名
            B列: "selector" => CSSセレクタ
            C列: "index" => ヒットしたDOM要素のインデックス番号
         ※上から順にはじめにマッチした行のみ適用
       ・メインコンテンツエリアのセレクタ:
         select_cont_main.csv
            A列: "name" => 設定名
            B列: "selector" => CSSセレクタ
            C列: "index" => ヒットしたDOM要素のインデックス番号
         ※上から順にはじめにマッチした行のみ適用
       ・サブコンテンツエリアのセレクタ:
         select_cont_subs.csv
            A列: "name" => 設定名
            B列: "selector" => CSSセレクタ
            C列: "index" => ヒットしたDOM要素のインデックス番号
            D列: "cabinet_name" => 格納先のコンテンツキャビネット名
         ※上から順に全行適用

4. 設定が終わったら、「書き出しを実行する」ボタンを押して、書き出しを実行してください。
  書き出しを実行しても、もとのデータが失われるわけではありません。
  意図したとおりに書き出されない場合は、設定を変えて何度も書き出すことができます。

5. 書き出したデータのダウンロード欄から、ZIPまたはTGZ形式でデータを取得できます。


------
(C)Tomoya Koyanagi.
http://www.pxt.jp/

