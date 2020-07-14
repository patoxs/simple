<?php
        $var_analytics_env = getenv('ANALYTICS');
        $var_analytics_cuenta = Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->analytics : '';
        if(!empty($var_analytics_env) && !empty($var_analytics_cuenta)):
    ?>

        <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
        ga('create', '<?= env('ANALYTICS') ?>', 'auto', 'instancia');
        ga('create', '{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->analytics : ''}}', 'auto', 'cuenta');
        ga('cuenta.send', 'pageview');
        ga('instancia.send', 'pageview');
        </script>

    <?php elseif(empty($var_analytics_env) && !empty($var_analytics_cuenta)): ?>
    
        <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
        ga('create', '{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->analytics : ''}}', 'auto', 'cuenta');
        ga('cuenta.send', 'pageview');
        </script>
    
    <?php elseif(!empty($var_analytics_env) && empty($var_analytics_cuenta)): ?>

        <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
        ga('create', '<?= env('ANALYTICS') ?>', 'auto', 'instancia');
        ga('instancia.send', 'pageview');
        </script>

    <?php endif; ?>