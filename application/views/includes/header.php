<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
    <?= (isset($pre_head)) ? $pre_head : '' ?> 
    <meta http-equiv="X-UA-Compatible" content="IE=8" />
    <meta charset="utf-8" />
    <title><?= $title ?></title>
    <meta name="keywords" content="<?= $meta_keywords ?>" />
    <meta name="description" content="<?= $meta_description ?>" />
    <meta property="og:site_name" content="<?= $this->config->item('website_name') ?>>" /> 
    <meta property="og:title" content="<?= $title ?>" />
    <meta property="og:description" content="<?= ($meta_og_description) ? $meta_og_description : $meta_description ?>" />    
    <meta property="og:image" content="<?= $meta_og_image ?>" />

    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="icon" type="image/ico" href="/favicon.ico" />

    <!--[if IE]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <!--[if lt IE 9]>
    <script src="http://ie7-js.googlecode.com/svn/version/2.1(beta3)/IE9.js"></script>
    <![endif]-->
 
    <script src="http://www.google.com/jsapi" type="text/javascript"></script>    
    <script type="text/javascript">
        google.load("jquery", "1.4.2");
        google.load("jqueryui", "1.8");
    </script>
    <script type="text/javascript" src="/js/main.js"></script>
    <script type="text/javascript" src="/js/jquery.sleep.js"></script>
    <script type="text/javascript" src="/js/jquery.textlimit.js"></script>
    <script type="text/javascript" src="/js/jquery.a-tools.js"></script>
    <script type="text/javascript" src="/js/jquery.ba-hashchange.min.js"></script>
    <script type="text/javascript" src="/js/jquery.fancybox-1.3.1.pack.js"></script>
    <script type="text/javascript" src="/js/jquery.tooltip.min.js"></script>
    <script type="text/javascript" src="/js/jquery.selectbox.js"></script>
    <link rel="stylesheet" href="/css/main.css" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/jquery.fancybox-1.3.1.css" type="text/css" media="all" />
    <?= (isset($post_head)) ? $post_head : '' ?> 
</head>
<body>