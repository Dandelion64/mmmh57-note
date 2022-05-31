<?php session_start(); ?>
<?php 
// 這寫法很好 連資料庫的 __connect_db.php 
// 最後有 session_start() 的 isset()
// 但瀏覽網頁不一定要連接資料庫 是好寫法
?>

<?php include __DIR__ . '/parts/html-head.php'; ?>
<?php include __DIR__ . '/parts/navbar.php'; ?>

<div class="container">
<h2>Welcome</h2>
</div>

<?php include __DIR__ . '/parts/scripts.php'; ?>
<?php include __DIR__ . '/parts/html-foot.php'; ?>