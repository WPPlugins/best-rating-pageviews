jQuery(function($){$(document).ready( function() {
 console.log('connect raiting');
 $('.raiting').click(function(){
	console.log('click .raiting');
	//var data = $(this).serializeArray(); // помещаем поля формы в массив json, для передачи в аякс обработчик
	var postId = $(this).siblings('.hidden').attr('postid'); // получаем id поста, которому ставят оценку
	he_voted = $.cookies.get('article'+postId); // проверяем есть ли кука?
	if(he_voted !== null){console.log('Юзер уже голосовал. клик отменен!'); return;}
//	var zapros = '.brpv_raiting_star_'+postId;
//	$(zapros).children('div').children('.raiting_info strong, .raiting_info img').toggle() // высвечиваем индикатор загрузки;
	
	$.ajax({ // старт аякс обработки
		type: "POST",
		dataType : "json",
		url:  brpvajax.brpvajaxurl,
		data: {
			action: "brpv_do_something",  // brpv_do_something - функция в php файле, в которой происходит обработка аякс запроса
			user_votes: user_votes,
			postId: postId
		},
		// data, а точнее $_REQUEST['data'] хранит массив с именами и значениями полей формы
			beforeSend : function() {
				// происходит непосредственно перед отправкой запроса на сервер.
				console.log('отработала beforeSend');
			},
			error : function() {
				// происходит в случае неудачного выполнения запроса.
				console.log('отработала error');
			},
		/*	dataFilter : function() {
				// происходит в момент прибытия данных с сервера. Позволяет обработать "сырые" данные, присланные сервером.
				console.log('отработала dataFilter');
			},*/
			success : function(answer) {
				// происходит в случае удачного завершения запроса
				console.log('отработала success');	
				
			//	console.log('2='+answer['status']);
				//console.log('3='+answer.status);
			//	$('.raiting_info img, .raiting_info strong').toggle() // убираем индикатор загрузки;
			//	console.table(answer); /* ОТЛАДОЧНАЯ ИНФОРМАЦИЯ. Что вернулось? */
				if (answer['status'] == 'success') {
					console.log('статус ответа от php = "success"');
					$.cookies.set('article'+postId, 123, {hoursToLive: 1}); // создаем куку о голосовании
					var zapros = '.brpv_raiting_star_'+postId;
					$(zapros).children('div').children('.raiting_info span').text(answer['total_rating_new']); // выводим числовое значение рейтинга
					var star_widht = answer['total_rating_new']*17 ; // ширина одной звездочки
					$(zapros).children('div').children('.raiting_votes').width(star_widht); // выводим звездочки
					$(zapros).children('div').children('.raiting_votes, .raiting_hover').toggle();
					$(zapros).children(".raiting").unbind(); 
					$(zapros).children('div').children('.raiting_hover').hide();
				}
			},
			complete : function() {
				// происходит в случае любого завершения запроса
				console.log('отработала complete');				
			},			
	});
	return;
 });
 
 // выбираем элементы, классы которых начинаются на brpv_raiting_star_
 $('[class ^= brpv_raiting_star_]').each(function() { 
	var curpostid = $(this).children(".hidden").attr('postid');
	console.log('curpostid = '+curpostid);	
	output_stars(curpostid);
 }); 
 
 
 function output_stars (postId) { // ф-я вывода звед
	console.log('стартовала output_stars');	
	console.log('output_stars: postId = '+postId);
	var zapros = '.brpv_raiting_star_'+postId; 
	var total_reiting = $(zapros).children(".hidden").attr('ratingvalue');
	var star_widht = total_reiting*17 ; // ширина одной звездочки
	$(zapros).children('div').children('.raiting_votes').width(star_widht); // выводим звездочки
	$(zapros).children('div').children('.raiting_info span').append(total_reiting); // выводим числовое значение рейтинга
	
	he_voted = $.cookies.get('article'+postId); // проверяем есть ли кука?
	if(he_voted == null){ console.log('output_stars: куки нет');	
		 /* наведение на звездочки */
		$(zapros+' .raiting').hover(function() {
			$(zapros).children('div').children('.raiting_votes, .raiting_hover').toggle();
		},
		function() {
			$(zapros).children('div').children('.raiting_votes, .raiting_hover').toggle();
		});
	}
 } 
 
 /* наведение на звездочки 
 $('.raiting').hover(function() {
	$('.raiting_votes, .raiting_hover').toggle();
	},
	function() {
	$('.raiting_votes, .raiting_hover').toggle();
 }); */
 var margin_doc = $(".raiting").offset(); // С помощью этих функций, можно узнавать координаты элемента на странице. Кроме этого, с помощью offset(), можно изменить координаты элемента. Имеется несколько вариантов использования функций.
 $(".raiting").mousemove(function(e){
	var widht_votes = e.pageX - margin_doc.left;
	if (widht_votes == 0) widht_votes = 1;
	user_votes = Math.ceil(widht_votes/17);  
	// обратите внимание переменная  user_votes должна задаваться без var, т.к. в этом случае она будет глобальной и мы сможем к ней обратиться из другой ф-ции (нужна будет при клике на оценке.
	$('.raiting_hover').width(user_votes*17);
 });
 /* end наведение на звездочки */

})}); // end jQuery

 /*  $.ajax({ // старт аякс обработки
        type: "POST",
		dataType : "json",
        url:  wlAjax.ajaxurl,
        data: {nonce : nonce, action: "tas_send_ajax", cur_id_poll: selected_form, data} , 
		// data, а точнее $_REQUEST['data'] хранит массив с именами и значениями полей формы
		    success : function(response) {
			console.table(response); /* ОТЛАДОЧНАЯ ИНФОРМАЦИЯ. Что вернулось?  
			console.log('11');
				if(response.status == "success") {
				console.log('success_send');
				$('#acf_'+response.num).html(response.message_success_send);
				console.log('success_send');
				
				$('.tas_button_ok').hide(700).parent().html(response.otvet);
				
				} else
				{
				console.log('not_send');
				$('#acf_'+response.num).html(response.message_not_send);
				console.log('not_send');
				}
			}
        });
		return;
	});*/

 
 
  /*  if ( typeof( send_pid_view ) === 'undefined' ) {
        return;
    }*/
 
 
 
 
   /* $.ajax({
        url: brpvajax.ajax_url,
        type: 'POST', 
        data: {
            action: 'post_view_set',
         //   pid: send_pid_view
        } 
    }); */
 


/*
total_reiting = 4.2; // итоговый ретинг
id_arc = 55; // id статьи 
var star_widht = total_reiting*17 ;
$('#raiting_votes').width(star_widht);
$('#raiting_info h5').append(total_reiting);
he_voted = null; //$.cookies.get('article'+id_arc); // проверяем есть ли кука?
if(he_voted == null){
$('#raiting').hover(function() {
      $('#raiting_votes, #raiting_hover').toggle();
	  },
	  function() {
      $('#raiting_votes, #raiting_hover').toggle();
});
var margin_doc = $("#raiting").offset();
$("#raiting").mousemove(function(e){
var widht_votes = e.pageX - margin_doc.left;
if (widht_votes == 0) widht_votes =1 ;
user_votes = Math.ceil(widht_votes/17);  
// обратите внимание переменная  user_votes должна задаваться без var, т.к. в этом случае она будет глобальной и мы сможем к ней обратиться из другой ф-ции (нужна будет при клике на оценке.
$('#raiting_hover').width(user_votes*17);
});
// отправка
$('#raiting').click(function(){console.log('click #raiting');
$('#raiting_info h5, #raiting_info img').toggle();


	$.ajax({
		type: 'POST',
        url:  wlAjax.ajaxurl,
		data: {id:id,num:num},
		success: function() {
			alert ('!');
		}
	});*/




/*$.get(
"raiting.php",
{id_arc: id_arc, user_votes: user_votes}, 
function(data){
	$("#raiting_info h5").html(data);
	$('#raiting_votes').width((total_reiting + user_votes)*17/2);
	$('#raiting_info h5, #raiting_info img').toggle();
	$.cookies.set('article'+id_arc, 123, {hoursToLive: 1}); // создаем куку 
	$("#raiting").unbind();
	$('#raiting_hover').hide();
	}
	  
								   });
} )*/
						  
	

	
	
	
	
	
	
	
	
	
	/*
	
	
 $(document).on('mouseover','.vote-block li',function() { // навели на зведочку
	var $element = $(this);
	var star = parseInt($element.text(),10); // получаем номер звездочки

	if($element.parent().parent().hasClass('disabled')) {
		// если уже проголосовал - возвращаем лож
		return false;
	}
	// в противном случае выводим сколько голосов хотим дать
	$('#rating-info').show().html(star +' ' + numStar(star, ['голос', 'голоса', 'голосов']));
 }).on('mouseleave','.vote-block li',function() {
	$('#rating-info').hide();
 });
 $(document).on('click','.vote-block li',function() {
	// клик по звездочке 
	var $element = $(this);
	var id = $element.parent().parent().data('id');
	var total = $element.parent().parent().data('total');
	var rating = $element.parent().parent().data('rating');
	var num = parseInt($element.text(),10);

	if($element.parent().parent().hasClass('disabled')) {
		return false;
	} 
	$.ajax({
		type: 'POST',
		url: '?vote=ajax',
		data: {id:id,num:num},
		success: function(pr) {
			if (pr === 'limit') {
				return false;
			} else {
				$element.parent().parent().addClass('disabled');
				$.cookie('vote-post-'+id, true, {expires: 7, path: '/' });
				$element.parent().find('.current span').css('width',pr+'%');
				total++;
				var abs = ((rating+num)/total);
				abs = (abs^0)===abs?abs:abs.toFixed(1);
				$element.parent().parent().find('span.rating-text').html('(<strong>'+total+'</strong> '+numStar(total, ['голос', 'голоса', 'голосов']) +', в среднем: <strong>'+abs+'</strong> из 5)');
			}
		}
	});
	return false;
 });

})});

function numStar(number, titles) {  
 cases = [2, 0, 1, 1, 1, 2];  
 return titles[ (number%100>4 && number%100<20)? 2 : cases[(number%10<5)?number%10:5] ];  
} */