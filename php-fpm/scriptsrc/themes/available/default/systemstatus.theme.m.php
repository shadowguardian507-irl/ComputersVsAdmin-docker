<style>
#SystemStatus{
font-family:"arial";
font-size:.6em;
position:fixed;
top:0em;
right:0em;
text-align:right;
padding-bottom:.8em;
padding-left:.8em;
padding-right:.5em;
padding-top:.25em;
border-bottom-left-radius:1em 1em;
z-index: 999;
<?php
if($initerror){
echo "background-color:rgba(255,0,0,0.3);";
}
else
{
echo "background-color:rgba(0,255,0,0.3);";
}
?>
}
</style>
