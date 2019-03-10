// any CSS you require will output into a single css file (app.css in this case)
import '../css/app.scss';

// loads the jquery package from node_modules
import $ from 'jquery';

// font-awesome
import '@fortawesome/fontawesome-free/css/all.min.css';
import '@fortawesome/fontawesome-free/js/all.js';

// Initialization, then to define ajax route
const page = window.page;

// Ajax call
function ajaxCall(params){
	$.get( 
		page.routes.ajaxSearch, // send to ajaxSearchAction into StoreController
		params.toString(), 
		function (filteredProducts){
			$('#productsList li').empty(); // remove former products and add the new ones:
			for (let i in filteredProducts){
				let product = filteredProducts[i];
				$('#productsList').append('<li class="mb-2" productId='+ product.id +'><p class="h3">'+ product.name +'</p><i>'+ product.reference +' - '+ product.price +'€ - '+ product.saleNoticeDate +' - '+ product.brand +'</i></li>');
			}
		},
		'json'
	);
	window.history.replaceState({}, '', 'store?' + params.toString());
}

$(document).ready(function() {
	// Change GET parameters depending on filter that has been changed
	let url = window.location.search;
	let params = new URLSearchParams(url);

	// SEARCH
	$('#searchButton').click(function(){
		params.set("search",$('#search').val());
		ajaxCall(params);
	});
	$(document).on('keyup', function(e){
		if(e.which == 13 && $('#search').is(':focus')) {
			params.set("search",$('#search').val());
			ajaxCall(params);
		}
	});

	// FILTER: Maximum Price 
	$('#maxPrice').change(function(){
		$("#maxPriceValue span:first").text(this.value);
		$("#maxPriceValue").removeClass('d-none');
		
		params.set("maxPrice", this.value);
		ajaxCall(params);
	});
	// to delete the filter value
	$('#maxPriceValue').click(function(){
		$("#maxPriceValue").addClass('d-none');

		params.delete("maxPrice");
		ajaxCall(params);
	});

	// FILTER: Brands
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
		} else {
			let index = this.value;
			newDisplayedBrands = brands.filter(brand => brand != index);
		}
		params.set("brands", JSON.stringify(newDisplayedBrands));
		ajaxCall(params);
	});

	// FILTER: "After" this Sale Notice Date
	$('#saleNoticeDate').change(function(){
		params.set("saleNoticeDate", this.value);
		ajaxCall(params);
	});
});


