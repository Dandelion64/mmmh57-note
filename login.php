<?php
$page_name = 'login';
?>
<?php include __DIR__ . '/parts/html-head.php'; ?>
<?php include __DIR__ . '/parts/navbar.php'; ?>

    <style>
        .form-group small.form-text {
            color: red;
        }
    </style>

<div class="container">

        <div id="info-bar" class="alert alert-info" role="alert" style="display: none">
            123
        </div>

    <div class="row">
        <div class="col-lg-6">
            <?php 
            // form 的 onsubmit 會在每次輸入欄位確定後觸發
            // 沒有 novalidate 屬性 所以是用 HTML5 Web API 來做驗證的
            ?>
            <form name="form1" method="post" onsubmit="return checkForm()">
                <div class="form-group">
                    <label for="email">email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <small id="emailHelp" class="form-text"></small>
                </div>
                <div class="form-group">
                    <label for="password">password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <small id="mobileHelp" class="form-text"></small>
                </div>
                <button type="submit" class="btn btn-primary">登入</button>
            </form>
        </div>
    </div>

</div>
<?php include __DIR__ . '/parts/scripts.php'; ?>
<script>
    // Ref: https://stackoverflow.com/questions/46155/how-can-i-validate-an-email-address-in-javascript 
    // useful 6000 up
    // const email_re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zAZ]{2,}))$/;   
    const email_re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
    const mobile_re = /^09\d{2}-?\d{3}-?\d{3}$/;

    function checkForm(){
        // Ref: https://api.jquery.com/jquery.post/
        // Ref2: https://www.w3school.com.cn/jquery/ajax_post.asp
        
        // Ref: https://api.jquery.com/serialize/#serialize 
        // 裡面有範例可以玩看看
        $.post('login-api.php', $(document.form1).serialize(), function(data){
            if(data.success){
                // show()
                // Ref: https://api.jquery.com/show/
                // Ref2: https://www.w3school.com.cn/jquery/effect_show.asp

                // 會讓其顯現 因為失敗後除了一直失敗就是成功
                // 所以不用用到 hide() 方法
                // 可以考慮用 closure 做有限次數登入成功的限制

                // Ref: https://api.jquery.com/text/#text-text
                $('#info-bar').show().text('登入成功');
                // 一秒之後跳轉回首頁
                setTimeout(function(){
                    location.href = 'index_.php';
                }, 1000);
            } else {
                $('#info-bar').show().text('帳號或密碼錯誤');
            }
        }, 'json');

        return false; 
        // 就是表單不送出資料 要改用 AJAX 
        // 跟寫在上面一樣意思
        // <form name="form1" method="post" onsubmit="return checkForm(); return false;">
    }
</script>
<?php include __DIR__ . '/parts/html-foot.php'; ?>