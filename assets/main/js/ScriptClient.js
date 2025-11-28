$(function(){
  $("#ConventionClient").hide();
  $("#PieceExonorationClient").hide();
  // Type Client toggle (add-form)
  $("#typeClient").change(function(){
    var type = $(this).val();
    if(type=='conventionner'){
      $("#ConventionClient").show();
      $("#ConventionClientUpdate").show();
    } else {
      $("#ConventionClient").hide();
      $("#ConventionClientUpdate").hide();
    }
  });
  // Exonoration toggle (add-form)
  $("#ExonorationClient input[type=radio]").click(function(){
    var exo = $(this).val();
    if(exo=='oui'){ $("#PieceExonorationClient").show(); }
    else { $("#PieceExonorationClient").hide(); }
  });

  // Delegated handler for all "Mod" buttons in tables
  $(document).on('click', '.updateClient, .updateClient a', function(e){
    e.preventDefault();
    var id = $(this).data('id') || $(this).attr('id') || $('a', this).attr('id');
    if(!id) return;
    $("#ClientId").val(id);

    $.ajax('pages/clients/ModifierClient.php', {
      type: 'GET',
      data: { id_client: id },
      success: function(html){
        $("#detail").html(html);
        try { var m = new bootstrap.Modal(document.getElementById('ModalUpdateClient')); m.show(); }
        catch(_) { $('#ModalUpdateClient').modal('show'); }
      },
      error: function(err){ console.log('Load error', err); }
    });
  });
});
