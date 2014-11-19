<?php
$data                   = (isset($data))? $data : array();
$data['page']           = 1;
$data['renderNextLink'] = 0;
?>
<script type="text/javascript">
    $(function() {
        function refreshContent()
        {
            var containerSelector   = '<?= $containerSelector ?>';
            var itemSelector        = '<?= $itemSelector ?>';
            var container           = $(containerSelector);
            var firstElement        = $(containerSelector + ' ' + itemSelector).first();
            var data                = <?= json_encode($data); ?>;
            data.minId              = $(firstElement).attr('id');
            if (typeof data.minId == "undefined")
            {
                data.minId          = 0;
            }
            $.ajax({
                type    : 'GET',
                url     : '<?= $refreshUrl ?>',
                data    : data,
                dataType: 'html',
                success : function(data, status, request)
                {
                    if (data.trim())
                    {
                        $(container).prepend(data);
                        $('p#no-content').hide();
                    }
                }
            });
        }
        var refreshId = setInterval(refreshContent, 5000);
    });
</script>