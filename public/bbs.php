<?php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

if (isset($_POST['body'])) {
  // POSTで送られてくるフォームパラメータ body がある場合

  $image_filename = null;
  if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
    // アップロードされた画像がある場合
    if (preg_match('/^image\//', $_FILES['image']['type']) !== 1) {
      // アップロードされたものが画像ではなかった場合
      header("HTTP/1.1 302 Found");
      header("Location: ./bbs.php");
    }

    // 元のファイル名から拡張子を取得
    $pathinfo = pathinfo($_FILES['image']['name']);
    $extension = $pathinfo['extension'];
    // 新しいファイル名を決める。他の投稿の画像ファイルと重複しないように時間+乱数で決める。
    $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.' . $extension;
    $filepath =  '/var/www/public/image/' . $image_filename;
    move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
  }
  
  // insertする
  $insert_sth = $dbh->prepare("INSERT INTO bbs (body, image_filename) VALUES (:body, :image_filename)");
  $insert_sth->execute([
      ':body' => $_POST['body'],
      ':image_filename' => $image_filename,
  ]);

  // 処理が終わったらリダイレクトする
  // リダイレクトしないと，リロード時にまた同じ内容でPOSTすることになる
  header("HTTP/1.1 302 Found");
  header("Location: ./bbs.php");
  return;
}

// ページング機能
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
// 1ページあたりの行数を決める
$count_per_page = 20;
// ページ数に応じてスキップする行数を計算
$skip_count = $count_per_page * ($page - 1);

$select_sth = null;
if (isset($_GET['search'])) {
	$search = '%' . $_GET['search'] . '%';
    // 絞り込み
	//件数取得
	$count_sth = $dbh->prepare('SELECT COUNT(*) FROM bbs WHERE body LIKE :search');
	$count_sth->bindParam(':search' , $search);
	$count_sth->execute();
	$count_all = $count_sth->fetchColumn();
    //本体取得
	$select_sth = $dbh->prepare('SELECT * FROM bbs WHERE body LIKE :search ORDER BY created_at DESC LIMIT :count_per_page OFFSET :skip_count');
    $select_sth->bindParam(':search' , $search);
} else {
    // 全件取得
	//件数取得
	$count_sth = $dbh->prepare('SELECT COUNT(*) FROM bbs');
	$count_sth->execute();
	$count_all = $count_sth->fetchColumn();
    //本体取得
    $select_sth = $dbh->prepare('SELECT * FROM bbs ORDER BY created_at DESC LIMIT :count_per_page OFFSET :skip_count');
}
$select_sth->bindParam(':count_per_page' , $count_per_page, PDO::PARAM_INT);
$select_sth->bindParam(':skip_count' , $skip_count, PDO::PARAM_INT);
$select_sth->execute();

if ($skip_count >= $count_all) {
    print('このページは存在しません!'); // スキップする行数が全行数より多かったらおかしいのでエラーメッセージ表示し終了
    return;
}
$link_array = [];// aタグ変換用
$last_page = floor(($count_all / $count_per_page)) + 1;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="css/bbs.css">
    <title>掲示板</title>
</head>
<body>
<div id="wrapper">


<h1>掲示板</h1>
<!--<button type="button" onclick="changeCSS()">ダークモード</button>-->
  <!-- フォームのPOST先はこのファイル自身にする -->
  <div class="contents">
  <h2>投稿</h2>
  <form  method="POST" action="./bbs.php" enctype="multipart/form-data">
    <textarea name="body"></textarea>
    <div>
      <input type="file" accept="image/*" name="image" id="imageInput">
	  <button type="submit">投稿する</button>
    </div>
  </form>
  </div>
  <div class="search" class="contents">
    <h3>検索</h3>
    <form method="GET" action="./bbs.php">
      <input type="text" name="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
      <button type="submit">検索</button>
      <?php if(!empty($_GET['search'])): ?>
  	    <a href="?search=">絞り込み解除</a>
      <?php endif; ?>
    </form>
  </div>
  <hr>
  
  <?php foreach($select_sth as $entry): ?>
    <dl class="contents">
  	<dt class="line"><span class="id">ID:<?= $entry['id'] ?></span><span class="date"><?= $entry['created_at'] ?></span></dt>
          <p id=">><?= $entry['id'] ?>"><?= nl2br(htmlspecialchars($entry['body'])) // 必ず htmlspecialchars() すること ?></p>
          <?php if(!empty($entry['image_filename'])): ?>
            <img class="image" src="/image/<?= $entry['image_filename'] ?>">
          <?php endif; ?>
    </dl>
    <?php $link_array[] = ">>{$entry['id']}"; ?>
  <?php endforeach; ?>
  
    <?php $j_a = json_encode($link_array); ?>
  </div>
</div>

<div class="pages">
  <a href="?page=1">最初</a>
  <?php if($page > 1): // 前のページがあれば表示 ?>
    <a href="?page=<?= $page - 1 ?>"> 前ページ </a>
  <?php endif; ?>
  <p><?= $page ?>ページ目</p>
  <?php if($page < $last_page): // 次のページがあれば表示 ?>
    <a href="?page=<?= $page + 1 ?>"> 次ページ </a>
  <?php endif; ?>
  <a href="?page=<?= $last_page ?>">最後</a>
</div>
<button id="scroll-to-top-btn">≫</button>
<script>
　document.addEventListener("DOMContentLoaded", () => {
　  const imageInput = document.getElementById("imageInput");
　  imageInput.addEventListener("change", () => {
　    if (imageInput.files.length < 1) {
　      // 未選択の場合
　      return;
　    }
　    if (imageInput.files[0].size > 5 * 1024 * 1024) {
　      // ファイルが5MBより多い場合
　      alert("5MB以下のファイルを選択してください。");
　      imageInput.value = "";
　    }
　  });
　});
　// アンカー機能
  let array = <?php echo $j_a; ?>;
  for(const val of array){
	var org = document.getElementById(val);
	var str = org.textContent;
	var re = />>\d{1,}/g; // >>数字 を検索対象
	var mat1 = str.replace(re, "<a href='#$&'>$&</a>"); // 対象をaタグで加工
	org.innerHTML = mat1; // 中身を戻す
  }
  function changeCSS(){
    document.getElementById("wrapper").classList.add("changeColor");
}
//ボタン
const scroll_to_top_btn = document.querySelector('#scroll-to-top-btn');

//クリックイベントを追加
scroll_to_top_btn.addEventListener( 'click' , scroll_to_top );

function scroll_to_top(){
	window.scroll({top: 0, behavior: 'smooth'});
};


//スクロール時のイベントを追加
window.addEventListener( 'scroll' , scroll_event );

function scroll_event(){
	
	if(window.pageYOffset > 400){
		scroll_to_top_btn.style.opacity = '1';
	}else	if(window.pageYOffset < 400){
		scroll_to_top_btn.style.opacity = '0';
	}
	
};
</script>
</body>
</html>
