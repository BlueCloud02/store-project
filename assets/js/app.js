// any CSS you require will output into a single css file (app.css in this case)
import '../css/app.scss';

// loads the jquery package from node_modules
import $ from 'jquery';

// font-awesome
import '@fortawesome/fontawesome-free/css/all.min.css';
import '@fortawesome/fontawesome-free/js/all.js';

// Initialization, then to define ajax route
const page = window.page;

/* ----------- AJAX CALL ---------*/
function ajaxCall(params){
	$.get( 
		page.routes.ajaxSearch, // send to ajaxSearchAction into StoreController
		params.toString(), 
		// RETURN
		function (data){
			//  Remove former products and pagination links
			$('#productsList li').remove(); 
			$('.pagination li').remove();

			// If there is no products, display special text
			if(data.nbProducts == 0){
				$('#productsList').append('<li>Aucun produit correspondant à vos critères n\'a été trouvé. </li>');
			} else { 
			// ELSE: 
				//Add the new products
				let filteredProducts = data.products;

				for (let i in filteredProducts){
					let product = filteredProducts[i];
					$('#productsList').append('<li class="mb-2" productId='+ product.id +'><p class="h3">'+ product.name +'</p><i>'+ product.reference +' - '+ product.price +'€ - '+ product.saleNoticeDate +' - '+ product.brand +'</i></li>');
				}

				// Pagination: links for pages & set page to 1
				if(data.nbPages > 1){
					let range = [...Array(data.nbPages).keys()];
					for(let i in range){
						let p = Number(i)+1;
						$('.pagination').append('<li class="page-item"><a class="page-link">'+ p +'</a></li>');
						if (p == params.get("page")){					
							$("#pagination li:eq("+ parseInt(i) +")").addClass('active');
						}
					}
				}
			}			
		},
		'json'
	);
	// Modify URL
	let lastCharachter = window.location.pathname.slice(-1);
	window.history.pushState({}, '', 'store?' + params.toString());	
}

/* ---------- "HYDRATE" FILTERS if params are already not null ---------- */
function hydrateSearchAndFilter(params){
	$("#search").val(params.get("search"));
	
	if(params.get("maxPrice") !== null){
		$("#maxPrice").val(params.get("maxPrice"));
		$("#maxPriceValue span:first").text(params.get('maxPrice'));
		$("#maxPriceValue").removeClass('d-none');
	} else {
		$("#maxPriceValue").addClass('d-none');
	}

	$("#saleNoticeDate").val(params.get("saleNoticeDate"));
	
	let brands = JSON.parse(params.get("brands"));
	$("#brands input[type=checkbox]").each(function() {
		$(this).prop("checked", false);
	});
	for(let i in brands){
		$("#brands input[type=checkbox][value="+ brands[i] +"]").prop("checked", true);
	}
}

$(document).ready(function() {
	let url = window.location.search;
	let params = new URLSearchParams(url);

	hydrateSearchAndFilter(params);
	$(window).on('popstate', function(event) {
		let newParams = new URLSearchParams(event.target.location.search);
 		
 		hydrateSearchAndFilter(newParams);
 		
 		ajaxCall(newParams);
 		
 		// Brands params has to be changed, because if the filter is changed, brands param is modified from the former one
 		if (! newParams.has("brands")){
 			params.delete("brands");
		} else {
			params.set("brands", newParams.get("brands"));
		};
	});

	/* ---------------- FILTERS, SEARCH & PAGINATION --------------- */
	// Change GET parameters depending on filter that has been changed
	// SEARCH
	$("#searchButton").click(function(){
		params.set("search",$('#search').val());
		params.set("page", 1);
		ajaxCall(params);
	});
	$(document).on('keyup', function(e){
		if(e.which == 13 && $('#search').is(':focus')) {
			params.set("search",$('#search').val());
			params.set("page", 1);
			ajaxCall(params);
		}
	});

	// FILTER: Maximum Price 
	$('#maxPrice').change(function(){
		$("#maxPriceValue span:first").text(this.value);
		$("#maxPriceValue").removeClass('d-none');
		
		params.set("page", 1);

		params.set("maxPrice", this.value);
		ajaxCall(params);
	});
	// to delete the filter value
	$('#maxPriceValue').click(function(){
		$("#maxPriceValue").addClass('d-none');

		params.set("page", 1);

		params.delete("maxPrice");
		ajaxCall(params);
	});

	// FILTER: Brands
	$('#brands :checkbox').change(function(){
		let JSONBrands;
		JSONBrands = params.has("brands") ? JSON.parse(params.get("brands")) : [];

		let newDisplayedBrands;
		if(this.checked) {
			let newBrands = JSONBrands;
			newBrands.push(this.value);
			newDisplayedBrands = newBrands;
		} else {
			let index = this.value;
			newDisplayedBrands = JSONBrands.filter(brand => brand != index);
		}
		params.set("brands", JSON.stringify(newDisplayedBrands));

		params.set("page", 1);

		ajaxCall(params);
	});

	// FILTER: "After" this Sale Notice Date
	$('#saleNoticeDate').change(function(){
		params.set("saleNoticeDate", this.value);
		params.set("page", 1);
		ajaxCall(params);
	});

	// CHANGE PAGE
	$('.pagination').on('click', 'a', function(e) {
		params.set("page", $(e.target).text());
		ajaxCall(params);
	});
});


