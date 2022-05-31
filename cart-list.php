<?php
require __DIR__ . '/__connect_db.php';

$pKeys = array_keys($_SESSION['cart']);
// echo json_encode($_SESSION['cart'], JSON_UNESCAPED_UNICODE);
// echo json_encode($pKeys, JSON_UNESCAPED_UNICODE);

$rows = []; // 預設值
$data_ar = []; // dict


if (!empty($pKeys)) {
    // 這裡蠻特別 參見下面的 echo 用括號去包
    $sql = sprintf("SELECT * FROM products WHERE sid IN(%s)", implode(',', $pKeys));

    // echo implode(',', $pKeys);
    // echo $sql;

    $rows = $pdo->query($sql)->fetchAll();

    foreach ($rows as $r) {
        $r['quantity'] = $_SESSION['cart'][$r['sid']];
        $data_ar[$r['sid']] = $r; // 數量: 商品

        // echo json_encode($r, JSON_UNESCAPED_UNICODE);
    }
}

?>
<?php include __DIR__ . '/parts/html-head.php'; ?>
<?php include __DIR__ . '/parts/navbar.php'; ?>

<div class="container">
    <div class="row">
        <div class="col">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th scope="col">
                            <i class="fas fa-trash-alt"></i>
                        </th>
                        <th scope="col">封面</th>
                        <th scope="col">書名</th>
                        <th scope="col">價格</th>
                        <th scope="col">數量</th>
                        <th scope="col">小計</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($_SESSION['cart'] as $sid => $qty) :
                        $item = $data_ar[$sid];
                    ?>
                        <tr class="p-item" data-sid="<?= $sid ?>">
                            <td>
                                <a href="#" onclick="removeProductItem(event)">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                            <td>
                                <img src="imgs/small/<?= $item['book_id'] ?>.jpg" alt="">
                            </td>
                            <td>
                                <?= $item['bookname'] ?>
                            </td>
                            <td class="price" data-price="<?= $item['price'] ?>"></td>
                            <td>
                                <?php 
                                // 這裡是用 data-sid 去指定 selected 的 option 的 
                                // 某些情況下 視窗稍窄時 option 中數字會看不到
                                ?>
                                <select class="form-control quantity" data-qty="<?= $item['quantity'] ?>" onchange="changeQty(event)">
                                    <?php for ($i = 1; $i <= 20; $i++) : ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </td>
                            <td class="sub-total"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="alert alert-primary" role="alert">
                總計: <span id="totalAmount"></span>
            </div>
            <?php if (isset($_SESSION['loginUser'])) : ?>
                <a href="save-orders.php" class="btn btn-success">結帳</a>
            <?php else : ?>
                <div class="alert alert-danger" role="alert">
                    請先登入會員再結帳
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>
<?php include __DIR__ . '/parts/scripts.php'; ?>
<script>
    // 這寫法不夠好 對小數不管用
    // 下面這個比較好
    // Ref: https://stackoverflow.com/questions/2254185/regular-expression-for-formatting-numbers-in-javascript
    // function format(num) {
    //    return num.toString().replace(/^[+-]?\d+/, function(int) {
    //        return int.replace(/(\d)(?=(\d{3})+$)/g, '$1,');
    //    });
    //}
    // Ref2: https://www.regextester.com/
    // /^[+-]?\d+/
    // 匹配 0-1 個 + - 符號後綴一個以上的數字位
    // /(\d)(?=(\d{3})+$)/g, g for global
    // (?=(\d{3})+$) 為第二個捕獲位
    // 從後面取一個以上的三位數的數字
    // (\d) 為第一個捕獲位 $1
    // 將之取代為 $1 後面會不斷去做這件事

    const dollarCommas = function(n) {
        return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
    };

    function removeProductItem(event) {
        event.preventDefault(); // 避免 <a> 的連結跳轉
        const tr = $(event.target).closest('tr.p-item')
        const sid = tr.attr('data-sid');

        // 用 get 方法去取 url 的 Request
        // Ref: https://api.jquery.com/jquery.get/
        // Ref2: https://www.w3school.com.cn/jquery/ajax_get.asp
        // $(selector).get(url,data,success(response,status,xhr),dataType)
        $.get('add-to-cart-api.php', {
            sid
        }, function(data) {
            tr.remove(); // 移除欄位
            countCartObj(data); // 在 parts/scripts.php
            // console.log(data);
            calPrices(); // 算價格 在下面
        }, 'json');
    }

    // val() 取值
    // Ref: https://api.jquery.com/val/#val
    // Ref2: https://www.w3school.com.cn/jquery/attributes_val.asp
    function changeQty(event) {
        let qty = $(event.target).val();
        let tr = $(event.target).closest('tr');
        let sid = tr.attr('data-sid'); // 把 data-sid 換掉

        // 上面有用到 qty 所以要取 qty
        $.get('add-to-cart-api.php', {
            sid,
            qty
        }, function(data) {
            countCartObj(data);
            // console.log(data);
            calPrices();
        }, 'json');

    }

    // jQuery 的 length 不太一樣
    // 是 jQuery Object 中的子元素數量
    // Ref: https://api.jquery.com/length/#length1
    function calPrices() {
        const p_items = $('.p-item');
        // console.log(p_items);
        // console.log([p_items.length] + 'ABC');
        let total = 0;
        if (!p_items.length) {
            alert('請先將商品加入購物車');
            location.href = 'product-list.php';
            return;
        }

        p_items.each(function(i, el) {
            // console.log($(el).attr('data-sid'));
            // let price = parseInt( $(el).find('.price').attr('data-price') );
            // let price = $(el).find('.price').attr('data-price') * 1;

            // Ref: https://api.jquery.com/find/#find-selector
            const $price = $(el).find('.price'); // 價格的 <td>

            // 各欄的單品價格
            // Ref: https://api.jquery.com/text/#text2
            $price.text('$ ' + $price.attr('data-price'));

            const $qty = $(el).find('.quantity'); // <select> combobox
            // 如果有的話才設定
            if ($qty.attr('data-qty')) {
                $qty.val($qty.attr('data-qty'));
            }
            $qty.removeAttr('data-qty'); // 設定完就移除
            // 所以不會留在 DOM 中

            const $sub_total = $(el).find('.sub-total');

            // 各欄的總價
            $sub_total.text('$ ' + dollarCommas($price.attr('data-price') * $qty.val()));

            // 總價
            total += $price.attr('data-price') * $qty.val();
        });

        $('#totalAmount').text('$ ' + dollarCommas(total));

    }
    calPrices(); // 無論如何呼叫一次 算錢並移除 data-qty
</script>
<?php include __DIR__ . '/parts/html-foot.php'; ?>