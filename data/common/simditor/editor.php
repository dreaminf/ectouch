<?php
  require dirname(__FILE__) . '/config.php';
  if(!$enable) die('没有编辑器使用权限'); //权限验证
  $lang = $_CFG['lang'] == 'en_us' ? 'en':'zh-cn';
  $lang = 'zh-cn';
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>ECTouch Editor</title>
<link rel="stylesheet" type="text/css" href="styles/simditor.css">
<script type="text/javascript" src="scripts/jquery.min.js"></script>
<script type="text/javascript" src="scripts/module.min.js"></script>
<script type="text/javascript" src="scripts/uploader.min.js"></script>
<script type="text/javascript" src="scripts/hotkeys.min.js"></script>
<script type="text/javascript" src="scripts/simditor.min.js"></script>
<style type="text/css">
body {margin:0px; padding:0px;}
</style>
</head>

<body>
<script type="text/plain" name="container" id="container"></script>
<script type="text/javascript">
<?php $item = isset($_GET['item']) ? htmlspecialchars($_GET['item']) : 'content';?>
(function() {
  $(function() {
    var cBox, editor, toolbar;
    Simditor.locale = 'en-US';
    toolbar = ['title', 'bold', 'italic', 'underline', 'strikethrough', 'color', '|', 'ol', 'ul', 'blockquote', 'table', '|', 'link', 'image', 'hr', '|', 'indent', 'outdent', 'alignment'];
    editor = new Simditor({
      textarea: $('#container'),
      placeholder: '这里输入文字...',
      toolbar: toolbar,
      pasteImage: true,
      defaultImage: 'images/image.png',
      upload: {
        url: '<?php echo $root_url;?>index.php?m=admin&a=uploader'
      }
    });
    cBox = $('#<?php echo $item;?>', parent.document);
    return editor.on('valuechanged', function(e) {
      return cBox.html(editor.getValue());
    });
  });
}).call(this);
</script>
</body>
</html>