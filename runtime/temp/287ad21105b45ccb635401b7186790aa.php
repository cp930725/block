<?php /*a:1:{s:80:"D:\phpstudy\PHPTutorial\WWW\thinkphp\tp\application\mobile\view\index\index.html";i:1561023981;}*/ ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>首页</title>
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/css/index.css">
    <script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
    
</head>

<body style="background-color: #36ceff">
    <div class="container-fluid">
        <header class="row">
            <div class="col-xs-12 col-sm-12 header">
                <div class="row">
                    <div class="col-xs-6 col-sm-6 header-logo">
                        <img src="/static/image/index/logo.png" width="100%">
                    </div>
                    <div class="col-xs-4 col-sm-4 header-user">
                        <img src="static/image/index/user.jpg" class="img-circle" width="100%">
                        
                        <p><?php echo htmlentities(app('session')->get('user.profile.nickname')); ?></p>
                        <br>
                        <p style="margin-top:0"><?php echo htmlentities(app('config')->get('hello.level')[app('session')->get('user.account.type')]['name']); ?></p>
                    </div>
                </div>
            </div>
        </header>
        <!-- 矿池 -->
        <div class="row">
            <img src="/static/image/index/timg.jpg" width="100%" height="100%">
            <div class="col-xs-12 ore">
                <div class="row">
                    <div class="col-xs-9">
                        <h4>我的算力：</h4>
                        <div><strong><?php echo htmlentities(app('session')->get('user.dashboard.power')); ?> <sup>Ghs</sup></strong></div>
                    </div>
                    <div class="float-right">
                        <div class="second" style="float: right">
                            <div class="small text-muted">倒计时</div>
                            <div class="expire">s</div>
                        </div>
                        <div hidden class="profit" style="float: right"><button class="ore-receive btn btn-secondary btn-profit">领取收益</button></div>
                    </div>
                </div>
                <div class="progress prog">
                    <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" >
                        <span class="sr-only">1-<?php echo htmlentities($config['complexity'] + $collect); ?> Complete</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <h4>矿池余量：</h4>
                        <div><strong><?php echo money($config['volume'] - $collect); ?></strong></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <h4>当前难度：</h4>
                        <div><strong><?php echo htmlentities($config['complexity'] + $collect); ?></strong></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 公告 -->
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-4 notice">
                <div class="card card-news">
                    <div class="card-header">
                        <div class="card-title f1"><a href="/news.html">新闻头条</a></div>
                        <div class="card-options">
                            <div class="text-muted mr-2">权威的数字货币快讯</div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <ul class="list-unstyled">
                            <?php if(is_array($news) || $news instanceof \think\Collection || $news instanceof \think\Paginator): $i = 0; $__LIST__ = $news;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$new): $mod = ($i % 2 );++$i;?>
                            <li class="media mb-3">
                                <img src="/upload/<?php echo htmlentities($new['image']); ?>" class="w-7 h-7 mr-3" />
                                <div class="media-body">
                                    <h5 class="mt-0 mb-1 text-truncate" style="max-width: 13rem;"><a href="/article/<?php echo htmlentities($new['id']); ?>.html" class="font-weight-light"><?php echo htmlentities($new['title']); ?></a></h5>
                                    <small class="text-muted"><?php echo htmlentities($new['date']); ?></small>
                                </div>
                            </li>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        </div>





    </div>
    <script type="text/javascript" src="/assets/js/require.min.js"></script>
    <script type="text/javascript" src="/static/js/global.js?3"></script>
    <script type="text/javascript" src="/static/js/pool.js"></script>
</body>
</html>