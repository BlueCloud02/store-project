// any CSS you require will output into a single css file (app.css in this case)
import '../css/app.scss';

// loads the jquery package from node_modules
import $ from 'jquery';

// font-awesome
import '@fortawesome/fontawesome-free/css/all.min.css';
import '@fortawesome/fontawesome-free/js/all.js';

// Initialization, then to define ajax route
const page = window.page;

function ajaxCall(params){
	$.get( 
		page.routes.ajaxSearch,
		params.toString(),
		function (data){
			// TO FILL
		},
		'json'
	);
	window.history.replaceState({}, '', 'store?' + params.toString());
}

$(document).ready(function() {
	// Change GET parameters depending on filter that has been changed
	let url = window.location.search;
	let params = new URLSearchParams(url);

	$('#searchButton').click(function(){
		params.set("search",$('#search').val());
		ajaxCall(params);
	});

	$('#minPrice').change(function(){
		params.set("minPrice", this.value);
		ajaxCall(params);
	});

	$('#brands :checkbox').change(function(){
		let brands;
		if(params.has("brands")){
			brands = JSON.parse(params.get("brands"));
		} else {
			brands = [];
		}

		let newDisplayedBrands;
		if(this.checked) {
			let newBrands = brands;
			newBrands.push(this.value);
			newDisplayedBrands = newBrands;
			console.log(newDisplayedBrands);
		} else {
			let index = this.value;
			newDisplayedBrands = brands.filter(brand => brand != index);
			console.log(newDisplayedBrands);
		}
		params.set("brands", JSON.stringify(newDisplayedBrands));
		ajaxCall(params);
	});

	$('#saleNoticeDate').change(function(){
		params.set("saleNoticeDate", this.value);
		ajaxCall(params);
	});
});


