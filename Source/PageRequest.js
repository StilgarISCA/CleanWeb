function doSubmit() {
   document.loadcustom.page.value = window.btoa( encodeURI( document.loadcustom.page.value ) );
   return true;
}
