<ul>
	<li no="1" id="m1"></li>
</ul>
<script>
var options = {
pdfOpenParams: {
	navpanes: 0,
	toolbar: 0,
	statusbar: 0,
	view:"FitV",
	pagemode:"thumbs",
	page: 1
},
forcePDFJS: true
};
PDFObject.embed("<?=G5_URL?>/device/monitors/img/hrest_mainerrorRS4_6.pdf", "#m1",options);

</script>
