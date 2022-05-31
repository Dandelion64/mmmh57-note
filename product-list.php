<?php
require __DIR__. '/__connect_db.php';
$perPage = 4;

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$cate = isset($_GET['cate']) ? intval($_GET['cate']) : 0;
$pageBtnQS = [];

// WHERE 1 相當於 WHERE true 
// 相當於 if (true) in JavaScript
$where = ' WHERE 1 ';
if(! empty($cate)){
    // .= 就是等於自身接上後面字串
    // 因為 AND 前面是 true 所以只看後面的條件
    $where .= " AND category_sid=$cate ";
    $pageBtnQS['cate'] = $cate;
}


// 總筆數
// 原本是 COUNT(1) 我習慣改成 COUNT(*)
$t_sql = "SELECT COUNT(*) FROM products $where ";
$totalRows = $pdo->query($t_sql)->fetch(PDO::FETCH_NUM)[0];

$totalPages = ceil($totalRows/$perPage); // 總頁數

if ($page<1) {
    $page=1;
    }

if ($page>$totalPages) {
    $page=$totalPages;
}

$rows = [];
// 如果有資料

if ($totalRows>0) {
    $sql = sprintf("SELECT * FROM products $where LIMIT %s, %s", ($page-1)*$perPage, $perPage);
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();
}

// 分類資料
$c_sql = "SELECT * FROM categories WHERE parent_sid=0";
$cates = $pdo->query($c_sql)->fetchAll();

?>
<?php include __DIR__ . '/parts/html-head.php'; ?>
<?php include __DIR__ . '/parts/navbar.php'; ?>

<div class="container">
<div class="row">
    <div class="col-md-3">
        <!-- 分類選單 -->
        <div class="btn-group-vertical" style="width: 100%">
            <a type="button" href="?" class="btn <?= empty($cate) ? 'btn-primary' : 'btn-outline-primary' ?>">所有商品</a>
            <?php foreach($cates as $c): ?>
            <a type="button" href="?cate=<?= $c['sid'] ?>" class="btn <?= $cate==$c['sid'] ? 'btn-primary' : 'btn-outline-primary' ?>">
                <?= $c['name'] ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-md-9">
        <div class="row">
            <div class="col">
                <nav aria-label="Page navigation example">
                    <ul class="pagination">
                        <li class="page-item <?= $page==1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?php
                            $pageBtnQS['page']=$page-1;
                            echo http_build_query($pageBtnQS)
                            ?>">
                                <i class="fas fa-arrow-circle-left"></i>
                            </a>
                        </li>
                        <?php for($i=1; $i<=$totalPages; $i++):
                            $pageBtnQS['page']=$i;
                            ?>
                            <li class="page-item <?= $i===$page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query($pageBtnQS) ?>">
                                    <?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page==$totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?php
                            $pageBtnQS['page']=$page+1;
                            echo http_build_query($pageBtnQS)
                            ?>">
                                <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="row">
            <?php foreach($rows as $r): ?>
            <div class="col-md-3 product-unit" data-sid="<?= $r['sid'] ?>">
                <div class="card" >
                    <img src="imgs/small/<?= $r['book_id'] ?>.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h6 class="card-title"><?= $r['bookname'] ?></h6>
                        <p class="card-text"><i class="fas fa-user-secret"></i> <?= $r['author'] ?></p>
                        <p class="card-text"><i class="fas fa-dollar-sign"></i> <?= $r['price'] ?></p>
                        <form>
                            <div class="form-group">
                                <select class="form-control qty" style="display: inline-block; width: auto">
                                    <?php for($i=1; $i<=10; $i++){ ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php } ?>
                                </select>
                                <button type="button" class="btn btn-primary add-to-cart-btn"><i class="fas fa-cart-plus"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach;?>
        </div>
    </div>
</div>

</div>
<?php include __DIR__ . '/parts/scripts.php'; ?>
<script>
    const btn = $('.add-to-cart-btn');

    btn.click(function(){
        const sid = $(this).closest('.product-unit').attr('data-sid');
        //const qty = $(this).prev().val();
        const qty = $(this).closest('.product-unit').find('.qty').val();

        //console.log({sid, qty});

        $.get('add-to-cart-api.php', {sid, qty}, function(data){
            countCartObj(data);
        }, 'json');
    });

</script>
<?php include __DIR__ . '/parts/html-foot.php'; ?>