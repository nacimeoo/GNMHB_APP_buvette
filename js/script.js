(function($) {

  "use strict";

  var initPreloader = function() {
    $(document).ready(function($) {
    var Body = $('body');
        Body.addClass('preloader-site');
    });
    $(window).load(function() {
        $('.preloader-wrapper').fadeOut();
        $('body').removeClass('preloader-site');
    });
  }

  // init Chocolat light box
	var initChocolat = function() {
		Chocolat(document.querySelectorAll('.image-link'), {
		  imageSize: 'contain',
		  loop: true,
		})
	}

  var initSwiper = function() {

    var swiper = new Swiper(".main-swiper", {
      speed: 500,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
    });

    var category_swiper = new Swiper(".category-carousel", {
      slidesPerView: 6,
      spaceBetween: 30,
      speed: 500,
      navigation: {
        nextEl: ".category-carousel-next",
        prevEl: ".category-carousel-prev",
      },
      breakpoints: {
        0: {
          slidesPerView: 2,
        },
        768: {
          slidesPerView: 3,
        },
        991: {
          slidesPerView: 4,
        },
        1500: {
          slidesPerView: 6,
        },
      }
    });

    var brand_swiper = new Swiper(".brand-carousel", {
      slidesPerView: 4,
      spaceBetween: 30,
      speed: 500,
      navigation: {
        nextEl: ".brand-carousel-next",
        prevEl: ".brand-carousel-prev",
      },
      breakpoints: {
        0: {
          slidesPerView: 2,
        },
        768: {
          slidesPerView: 2,
        },
        991: {
          slidesPerView: 3,
        },
        1500: {
          slidesPerView: 4,
        },
      }
    });

    var products_swiper = new Swiper(".products-carousel", {
      slidesPerView: 5,
      spaceBetween: 30,
      speed: 500,
      navigation: {
        nextEl: ".products-carousel-next",
        prevEl: ".products-carousel-prev",
      },
      breakpoints: {
        0: {
          slidesPerView: 1,
        },
        768: {
          slidesPerView: 3,
        },
        991: {
          slidesPerView: 4,
        },
        1500: {
          slidesPerView: 6,
        },
      }
    });
  }

  var initProductQty = function(){

    $('.product-qty').each(function(){

      var $el_product = $(this);
      var quantity = 0;

      $el_product.find('.quantity-right-plus').click(function(e){
          e.preventDefault();
          var quantity = parseInt($el_product.find('#quantity').val());
          $el_product.find('#quantity').val(quantity + 1);
      });

      $el_product.find('.quantity-left-minus').click(function(e){
          e.preventDefault();
          var quantity = parseInt($el_product.find('#quantity').val());
          if(quantity>0){
            $el_product.find('#quantity').val(quantity - 1);
          }
      });

    });

  }

  // init jarallax parallax
  var initJarallax = function() {
    jarallax(document.querySelectorAll(".jarallax"));

    jarallax(document.querySelectorAll(".jarallax-keep-img"), {
      keepImg: true,
    });
  }

  // document ready
  $(document).ready(function() {

    initPreloader();
    initSwiper();
    initProductQty();
    initJarallax();
    initChocolat();
    chargerProduits('');

  }); // End of a document

})(jQuery);



window.chargerProduits = function(categorie = '') {
    const conteneurProduits = document.getElementById('liste-produits');

    if (conteneurProduits) {
        conteneurProduits.innerHTML = '<p class="text-center w-100">Chargement en cours...</p>';

        let url = 'api/getAllproduit.php';
        if (categorie !== '') {
            url += '?categorie=' + encodeURIComponent(categorie);
        }

        fetch(url)
            .then(response => response.json())
            .then(produits => {
                conteneurProduits.innerHTML = ''; 

                if(produits.length === 0) {
                    const isEventActive = document.getElementById('info-active-event').style.display !== 'none';
                    
                    if (!isEventActive) {
                        conteneurProduits.innerHTML = '<div class="w-100 text-center mt-5"><i class="bi bi-shop fs-1 text-muted d-block mb-3"></i><p class="text-muted fw-bold fs-5">Veuillez démarrer un événement pour ouvrir la caisse.</p></div>';
                    } else {
                        conteneurProduits.innerHTML = '<p class="text-center w-100 mt-5">Aucun produit disponible dans ce lieu.</p>';
                    }
                    return;
                }

                produits.forEach(produit => {
                    let imagePath = `${produit.image}`;
                    const enRupture = parseInt(produit.quantiteStock) <= 0;
                    const classeRupture = enRupture ? ' product-item--rupture' : '';

                    const carteHTML = `
                        <div class="col">
                          <div class="product-item${classeRupture}">
                            ${enRupture ? '<span class="product-item__rupture-badge">Rupture de stock</span>' : ''}
                            <figure class="product-item__fig">
                              <img src="${imagePath}" class="product-item__img" alt="${produit.nomProduit}">
                            </figure>
                            <h3 class="product-item__name">${produit.nomProduit}</h3>
                            <span class="price">${produit.Prix} €</span>

                            <div class="d-flex align-items-center justify-content-between mt-2">
                              <div class="input-group product-qty">
                                  <span class="input-group-btn">
                                      <button type="button" class="quantity-left-minus btn btn-danger btn-number" data-type="minus" ${enRupture ? 'disabled' : ''}>
                                        <svg width="16" height="16"><use xlink:href="#minus"></use></svg>
                                      </button>
                                  </span>
                                  <input type="text" id="qty-${produit.idproduit}" name="quantity" class="form-control input-number" value="1" ${enRupture ? 'disabled' : ''}>
                                  <span class="input-group-btn">
                                      <button type="button" class="quantity-right-plus btn btn-success btn-number" data-type="plus" ${enRupture ? 'disabled' : ''}>
                                          <svg width="16" height="16"><use xlink:href="#plus"></use></svg>
                                      </button>
                                  </span>
                              </div>
                              <button class="btn-add-cart"
                                  data-id="${produit.idproduit}"
                                  data-nom="${produit.nomProduit.replace(/"/g, '&quot;')}"
                                  data-prix="${produit.Prix}"
                                  data-image="${imagePath}"
                                  onclick="ajouterAuPanier(${produit.idproduit})"
                                  ${enRupture ? 'disabled' : ''}>
                                  <svg width="20" height="20"><use xlink:href="#cart"></use></svg>
                              </button>
                            </div>
                          </div>
                        </div>
                    `;
                    conteneurProduits.innerHTML += carteHTML;
                });

            })
            .catch(error => {
                console.error('Erreur :', error);
                conteneurProduits.innerHTML = '<p class="text-danger w-100 text-center">Impossible de charger les produits.</p>';
            });
    }
};

function demarrerEvent() {
    const nom = document.getElementById('event-nom').value;
    const entrepotSelect = document.getElementById('event-entrepot');
    const idEntrepot = entrepotSelect.value;
    const nomEntrepot = entrepotSelect.options[entrepotSelect.selectedIndex].text;

    if (!nom) return alert("Veuillez saisir un nom d'événement.");

    fetch('api/startEvent.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            nom: nom, 
            idEntrepot: idEntrepot,
            nomEntrepot: nomEntrepot
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('form-start-event').style.display = 'none';
            document.getElementById('info-active-event').style.display = 'flex';
            document.getElementById('display-event-nom').innerText = nom;
            document.getElementById('display-event-lieu').innerText = nomEntrepot;
            
            window.chargerProduits('');
        } else {
            alert("Erreur : " + data.message);
        }
    });
}

function cloturerEvent() {
    fetch('api/closeEvent.php')
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('form-start-event').style.display = 'flex';
            document.getElementById('info-active-event').style.display = 'none';
            document.getElementById('event-nom').value = '';
            
            window.chargerProduits('');
        }
    });
}



