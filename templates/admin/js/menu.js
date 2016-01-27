var active_menu_count = 0;

//--------------------------------------------------------------------------
function set_active_menu_count(val) {
	active_menu_count = val;
}

//--------------------------------------------------------------------------
function toggle_meta(){
	if(document.getElementById('row_meta_2').style.display == ""){
		document.getElementById('meta_tags_status').value = "closed";
		document.getElementById('row_meta_2').style.display = "none";
		document.getElementById('row_meta_3').style.display = "none";
		document.getElementById('row_meta_4').style.display = "none";
		document.getElementById('meta_close').style.display = "none";
		document.getElementById('meta_show').style.display = "";
	}else{
		document.getElementById('meta_tags_status').value = "opened";
		document.getElementById('row_meta_2').style.display = "";
		document.getElementById('row_meta_3').style.display = "";
		document.getElementById('row_meta_4').style.display = "";
		document.getElementById('meta_close').style.display = "";
		document.getElementById('meta_show').style.display = "none";
	}
};

//--------------------------------------------------------------------------
function ContentType_OnChange(val){
	if(val == "link"){
		document.getElementById("link_row_1").style.display = "";			
		document.getElementById("link_row_2").style.display = "";
		document.getElementById("page_row_1").style.display = "none";
		document.getElementById("page_row_2").style.display = "none";
		//document.getElementById("page_row_3").style.display = "";
		document.getElementById("row_meta_1").style.display = "none";
		if(document.getElementById("comments_allowed_1")) document.getElementById("comments_allowed_1").style.display = "none";
		if(document.getElementById("comments_allowed_2")) document.getElementById("comments_allowed_2").style.display = "none";
		if(document.getElementById("comments_allowed_3")) document.getElementById("comments_allowed_3").style.display = "none";
	}else{
		document.getElementById("link_row_1").style.display = "none";				
		document.getElementById("link_row_2").style.display = "none";
		document.getElementById("page_row_1").style.display = "";
		document.getElementById("page_row_2").style.display = "";
		//document.getElementById("page_row_3").style.display = "none";
		document.getElementById("row_meta_1").style.display = "";
		document.getElementById('meta_close').style.display = "none";
		document.getElementById('meta_show').style.display = "";
		if(document.getElementById("comments_allowed_1")) document.getElementById("comments_allowed_1").style.display = "";
		if(document.getElementById("comments_allowed_2")) document.getElementById("comments_allowed_2").style.display = "";
		if(document.getElementById("comments_allowed_3")) document.getElementById("comments_allowed_3").style.display = "";
	}
	document.getElementById("row_meta_2").style.display = "none";
	document.getElementById("row_meta_3").style.display = "none";
	document.getElementById("row_meta_4").style.display = "none";
}

//--------------------------------------------------------------------------
function toggle_menu_block(ind){
	var old_status = appGetMenuStatus(ind);
	if(!jQuery("#side_box_content_"+ind)){
		if(document.getElementById("side_box_content_"+ind).style.display == ""){
			document.getElementById("side_box_content_"+ind).style.display = "none";
		}else{
			document.getElementById("side_box_content_"+ind).style.display = "";
		}
	}else{
		jQuery("#side_box_content_"+ind).slideToggle("fast");		
	}
	save_menu_status(ind, old_status);				
};

//--------------------------------------------------------------------------
// save menu blocks status 
function toggle_menus(status){
	for(var i = 0; i < active_menu_count; i++){
		appSetCookie("side_box_content_"+i,((status == 1) ? "maximized" : "minimized"),14);
		if(status == 1){
			if(!jQuery("#side_box_content_"+i)) document.getElementById("side_box_content_"+i).style.display = "";
			else if(!jQuery("#side_box_content_"+i).is(":visible")) jQuery("#side_box_content_"+i).show("fast");
		}else{
			if(!jQuery("#side_box_content_"+i)) document.getElementById("side_box_content_"+i).style.display = "none";
			else if(jQuery("#side_box_content_"+i).is(":visible")) jQuery("#side_box_content_"+i).hide("fast");			
		}
	}
}

//--------------------------------------------------------------------------
// save menu blocks status 
function save_menu_status(ind, old_status){   
	for(var i = 0; i < active_menu_count; i++){
		if(i == ind){
			if(old_status == "none"){
				appSetCookie("side_box_content_"+i,"maximized",14);
			}else{
				appSetCookie("side_box_content_"+i,"minimized",14);
			}					
		}else{
			var status = document.getElementById("side_box_content_"+i).style.display;
			if(status == "none"){
				appSetCookie("side_box_content_"+i,"minimized",14);
			}else{
				appSetCookie("side_box_content_"+i,"maximized",14);
			}		
		}
	}
}

//--------------------------------------------------------------------------
// change icon
function change_icon(val){
	var icon_img_src = 'images/no_image.png';
	
	jQuery('.loading_img').show();
	
	jQuery.ajax({
		url: "ajax/handler.ajax.php",
		global: false,
		type: "POST",
		data: ({template : val, check_key : "apphpbd"}),
		dataType: "html",
		async:false,
		error: function(html){
			jQuery('.loading_img').hide();
			alert("AJAX: cannot connect to the server or server response error! Please try again later.");
		},
		success: function(html){			
			var obj = jQuery.parseJSON(html);            			
			if(obj.status == "1"){
				jQuery("#template_name").html(obj.template_name);
				jQuery("#template_direction").html(obj.template_direction);
				jQuery("#template_description").html(obj.template_description);
				jQuery("#template_license").html(obj.template_license);
				jQuery("#template_version").html(obj.template_version);
				jQuery("#template_layout").html(obj.template_layout);
				jQuery("#template_menus").html(obj.template_menus);
				if(val != "") icon_img_src = 'templates/' + val + '/' + obj.template_icon;
			}else{
				jQuery("#template_name").html("");
				jQuery("#template_direction").html("");
				jQuery("#template_description").html("");
				jQuery("#template_license").html("");
				jQuery("#template_version").html("");
				jQuery("#template_layout").html("");
				jQuery("#template_menus").html("");
			}
			jQuery('.loading_img').hide();
			jQuery("#template_icon").attr("src", icon_img_src);				
		}
	});	
}

//--------------------------------------------------------------------------
// collapse/expand navigation panel
function toggle_navigation_panel(status){
	appSetCookie("nav_panel_state",((status == 1) ? "expanded" : "collapsed"),14);
	if(status == 0){
		jQuery('#navColumnLeftWrapper').hide();
		jQuery('#imgCollapse').hide();
		jQuery('#imgExpand').show();	
	}else{
		jQuery('#navColumnLeftWrapper').show();
		jQuery('#imgCollapse').show();
		jQuery('#imgExpand').hide();		
	}	
}

