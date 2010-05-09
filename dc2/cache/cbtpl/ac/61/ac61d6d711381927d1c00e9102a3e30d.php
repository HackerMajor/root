<!-- Start Divider Bottom -->
<div id="divider2"></div>
<!-- End Divider Bottom -->

<!-- Start Level 5 -->
<div id="level5">

<!-- Start Services  -->
<div id="services">
<h1>Domaines d'intervention</h1>
<ul>
<li><a href="./index.php">Droit de l'immigration</a></li>
<li><a href="./index.php">Droit de la famille</a></li>
<li><a href="./index.php">Litiges liés au code de la route</a></li>
</ul>
</div>
<!-- End Services -->
  <div id="blogextra">
    <?php publicWidgets::widgetsHandler('extra');  ?>
 </div> <!-- End #blogextra -->a
  
</div>
<!-- End of Level 5 -->

</div>
<!-- END OF WRAPPER -->
</div>
<!-- END OF WRAPPER-->


<!-- Start Footer -->
<div id="footer">

<!-- Start Footer Wrapper -->

<div id="footer_textwrapper">
<!-- Start Footer Icons -->
<div id="footer_icons">
</div>
<!-- End Footer Icons -->

<!-- Start Copyright -->
<div id="footer_text">Copyright © 2010 Jessie Foulhioux | Réalisation Cédric Levasseur | <?php printf(__("Powered by %s"),"<a href=\"http://dotclear.org/\">Dotclear</a>"); ?> </div>
<!-- End Copyright -->
 
</div>
<!-- End Footer Wrapper -->

</div>
<!-- End Footer -->
<?php if ($core->hasBehavior('publicFooterContent')) { $core->callBehavior('publicFooterContent',$core,$_ctx);} ?>
