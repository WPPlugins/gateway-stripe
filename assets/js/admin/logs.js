jQuery(document).ready(function($){
	var logs = {
			init: function(){
				$('#log-entries').DataTable();
				$('.dataTables_length select').show();
			}
	}
	logs.init();
})