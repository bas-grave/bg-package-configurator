document.addEventListener('DOMContentLoaded', function () {
    let selectedProducts = [];
    let debounceTimer = null;

    Fancybox.bind("[data-fancybox]", {
    });

    // Handle clicking on products (simple & variable)
    document.addEventListener('click', function (event) {
        let productItem = event.target.closest('.package-product-item');
        let isVariation = event.target.closest('.package-variation-item'); // Detect if variation was clicked
        if (!productItem || isVariation) return;

        event.preventDefault();

        let productId = productItem.getAttribute('data-product-id');
        let productName = productItem.getAttribute('data-product-name');
        let productType = productItem.getAttribute('data-product-type');
        let productImage = productItem.querySelector('img') ? productItem.querySelector('img').src : '';

        // Handle Variable Products (Open FancyBox)
        if (productType === 'variable') {
            let variationBox = document.querySelector('#package-variation-box-' + productId);

            if (variationBox) {
                let popupContent = document.createElement('div');
                popupContent.classList.add('global-popup');

                let variationsHtml = `<h3>Kies variatie van ${productName}</h3><div class="package-variations">`;
                variationBox.querySelectorAll('.package-select-variation').forEach(button => {
                    let variationId = button.getAttribute('data-variation-id');
                    let variationName = button.getAttribute('data-product-name');
                    let variationImage = button.getAttribute('data-product-image') || productImage;

                    let isSelected = selectedProducts.some(p => p.key === `${productId}-${variationId}`) ? 'package-product-item--selected' : '';

                    variationsHtml += `
                        <div class="package-product-item package-variation-item ${isSelected}" 
                            data-variation-id="${variationId}" 
                            data-product-id="${productId}" 
                            data-product-name="${variationName}">
                            
                            <div class="package-product-item__inner">
                                <figure class="package-product-item__image wrap-1-1">
                                    <img src="${variationImage}" alt="${variationName}">
                                </figure>
                                <div class="package-product-item__info">
                                    <p class="package-product-item__title">${variationName}</p>
                                </div>
                            </div>
                        </div>
                    `;
                });
                variationsHtml += `</div>
                    <button class="close-fancybox button" title="Keuze bevestigen en popup sluiten" style="margin-top: 24px;">
                        Keuze bevestigen
                    </button>`;

                popupContent.innerHTML = variationsHtml;

                Fancybox.show([{ src: popupContent, type: 'html' }]);

                setTimeout(() => {
                    let variationContainer = document.querySelector('.global-popup .package-variations');

                    if (variationContainer) {
                        variationContainer.addEventListener('click', function (event) {
                            let variationItem = event.target.closest('.package-variation-item');
                            if (!variationItem) return;
                            
                            event.preventDefault();
                            event.stopPropagation();

                            let variationId = variationItem.getAttribute('data-variation-id');
                            let variationName = variationItem.getAttribute('data-product-name');
                            let variationImage = variationItem.querySelector('img') ? variationItem.querySelector('img').src : productImage;
                            let key = `${productId}-${variationId}`;

                            if (debounceTimer) return;
                            debounceTimer = setTimeout(() => debounceTimer = null, 200);

                            let existingIndex = selectedProducts.findIndex(p => p.key === key);

                            if (existingIndex !== -1) {
                                selectedProducts.splice(existingIndex, 1); // Remove the selected variation
                                variationItem.classList.remove('package-product-item--selected');
                            
                                // Check if any variations of this product are still selected
                                let hasOtherSelected = selectedProducts.some(p => p.id === productId);
                                if (!hasOtherSelected) {
                                    let parentProduct = document.querySelector(`.package-product-item[data-product-id="${productId}"]`);
                                    if (parentProduct) {
                                        parentProduct.classList.remove('package-product-item--selected');
                                    }
                                }
                            } else {
                                selectedProducts.push({ key, id: productId, name: variationName, variationId: variationId, image: variationImage });
                                variationItem.classList.add('package-product-item--selected');
                            
                                // Add the "selected" class to the parent product in the list
                                let parentProduct = document.querySelector(`.package-product-item[data-product-id="${productId}"]`);
                                if (parentProduct) {
                                    parentProduct.classList.add('package-product-item--selected');
                                }
                            }                            

                            updatePackageOverview();
                        });

                        // Close Fancybox when "Done" button is clicked
                        document.querySelector('.close-fancybox').addEventListener('click', function () {
                            Fancybox.close();
                        });
                    }
                }, 100);
            } else {
                console.error('Variation box not found for product:', productId);
            }
        } else {
            // Handle Simple Product Selection (Toggle)
            let key = productId;

            if (!selectedProducts.some(p => p.key === key)) {
                selectedProducts.push({ key, id: productId, name: productName, image: productImage });
                productItem.classList.add('package-product-item--selected');
            } else {
                selectedProducts = selectedProducts.filter(product => product.key !== key);
                productItem.classList.remove('package-product-item--selected');
            }

            updatePackageOverview();
        }
    });

    function updatePackageOverview() {
        let overviewList = document.querySelector('#package-selected-products');
        let countDisplay = document.querySelector('#package-product-count');
        let countNumber = document.querySelector('#product-count-number');
        let requestQuoteButton = document.querySelector('#request-quote-button'); // Get the button
    
        if (!overviewList || !countDisplay || !countNumber || !requestQuoteButton) return;
        
        overviewList.innerHTML = '';
    
        selectedProducts.forEach(product => {
            let listItem = document.createElement('li');
            listItem.classList.add('selected-product');
            listItem.innerHTML = `
                <img src="${product.image || ''}" width="40" height="40">
                ${product.name} 
                <button type="button" class="remove-product" data-key="${product.key}">X</button>
            `;
            overviewList.appendChild(listItem);
        });
    
        // Update count and show/hide it dynamically
        let productCount = selectedProducts.length;
        countNumber.textContent = productCount;
    
        if (productCount > 0) {
            countDisplay.style.display = 'block';
        } else {
            countDisplay.style.display = 'none';
        }
    
        let textArea = document.querySelector('.form-package-items textarea');
        if (textArea) {
            textArea.value = selectedProducts.map(product => '- ' + product.name).join("\n");
        }    
    
        // Fade in the "Request Quote" button if at least 3 products are selected, otherwise fade out
        if (productCount >= 3) {
            requestQuoteButton.style.display = 'block';
        } else {
            requestQuoteButton.style.display = 'none';
        }
    }
    
    // Remove product when clicking the "X" button
    document.addEventListener('click', function (event) {
        let removeButton = event.target.closest('.remove-product');
        if (!removeButton) return;

        let key = removeButton.getAttribute('data-key');
        selectedProducts = selectedProducts.filter(product => product.key !== key);

        let productItem = document.querySelector(`.package-product-item[data-product-id="${key.split('-')[0]}"]`);
        if (productItem) productItem.classList.remove('package-product-item--selected');

        updatePackageOverview();
    });
});
