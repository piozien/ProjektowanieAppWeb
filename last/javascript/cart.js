/**
 * Obsługuje dodawanie produktu do koszyka bez przeładowania strony
 * @param {HTMLFormElement} form - Formularz dodawania do koszyka
 * @param {Event} event - Obiekt zdarzenia formularza
 * @returns {boolean} - Zawsze false, aby zapobiec standardowemu wysłaniu formularza
 * @description Dodaje produkt do koszyka bez przeładowania strony, aktualizuje licznik produktów w koszyku i wyświetla wizualne potwierdzenie dodania.
 */
function addToCart(form, event) {
    // Zapobiegaj standardowemu wysłaniu formularza
    event.preventDefault();
    
    // Przygotuj dane formularza do wysłania
    const formData = new FormData(form);

    // Wyślij żądanie asynchronicznie
    fetch('?idp=sklep', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Znajdź i zaktualizuj licznik produktów w koszyku
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            let currentCount = parseInt(cartCount.textContent) || 0;
            currentCount += parseInt(formData.get('quantity')) || 1;
            cartCount.textContent = currentCount;
        }

        // Pokaż wizualne potwierdzenie dodania do koszyka
        const button = form.querySelector('button[type="submit"]');
        const originalText = button.textContent;
        button.textContent = 'Dodano do koszyka!';
        button.style.backgroundColor = '#4CAF50';
        
        // Przywróć oryginalny wygląd przycisku po 2 sekundach
        setTimeout(() => {
            button.textContent = originalText;
            button.style.backgroundColor = '';
        }, 2000);
    });

    return false;
}

/**
 * Aktualizuje ilość produktu w koszyku bez przeładowania strony
 * @param {number} productId - ID produktu do zaktualizowania
 * @param {string} action - Rodzaj akcji ('add' lub 'remove')
 * Obsługuje zwiększanie i zmniejszanie ilości produktu w koszyku, aktualizuje
 * wyświetlaną ilość, sumę koszyka oraz usuwa produkt gdy ilość spadnie do zera.
 */
function updateCartQuantity(productId, action) {
    // Przygotuj URL w zależności od akcji (dodawanie lub usuwanie)
    const url = action === 'add' ? 
        `?idp=sklep2&add_one=${productId}` : 
        `?idp=sklep2&remove_one=${productId}`;

    // Wyślij żądanie asynchronicznie
    fetch(url)
        .then(response => response.text())
        .then(() => {
            // Znajdź element wyświetlający ilość dla tego produktu
            const quantityElement = document.querySelector(`[onclick*="${productId}"]`)
                .parentElement.querySelector('.quantity-value');
            
            // Zaktualizuj wyświetlaną ilość
            let currentQuantity = parseInt(quantityElement.textContent);
            if (action === 'add') {
                currentQuantity++;
            } else {
                currentQuantity--;
            }
            quantityElement.textContent = currentQuantity;

            // Przelicz i zaktualizuj sumę koszyka
            updateCartTotal();

            // Obsłuż przypadek, gdy ilość spadła do zera
            if (currentQuantity === 0) {
                const cartItem = quantityElement.closest('.cart-item');
                // Dodaj efekt zanikania
                cartItem.style.opacity = '0';
                // Usuń element po zakończeniu animacji
                setTimeout(() => {
                    cartItem.remove();
                    // Sprawdź czy koszyk jest pusty i wyświetl odpowiedni komunikat
                    if (document.querySelectorAll('.cart-item').length === 0) {
                        const cartItems = document.querySelector('.cart-items');
                        if (cartItems) {
                            cartItems.innerHTML = '<div class="empty-cart">' +
                                '<i class="fas fa-shopping-cart"></i>' +
                                '<p>Twój koszyk jest pusty</p>' +
                                '<a href="?idp=sklep" class="continue-shopping">Kontynuuj zakupy</a>' +
                                '</div>';
                        }
                    }
                }, 300);
            }

            // Zaktualizuj licznik w nagłówku strony
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                let count = parseInt(cartCount.textContent) || 0;
                count += (action === 'add' ? 1 : -1);
                cartCount.textContent = Math.max(0, count);
            }
        });
}

/**
 * Przelicza i aktualizuje sumę koszyka
 * Oblicza całkowitą wartość koszyka na podstawie cen i ilości wszystkich
 * produktów, a następnie aktualizuje wyświetlaną sumę w formacie polskim (z przecinkiem).
 */
function updateCartTotal() {
    // Znajdź wszystkie produkty w koszyku
    const cartItems = document.querySelectorAll('.cart-item');
    let total = 0;

    // Oblicz sumę dla każdego produktu
    cartItems.forEach(item => {
        const priceText = item.querySelector('.cart-item-price span').textContent;
        const quantity = parseInt(item.querySelector('.quantity-value').textContent);
        // Wyciągnij cenę z tekstu i przekonwertuj na liczbę
        const price = parseFloat(priceText.replace('Cena: ', '').replace(' zł', '').replace(',', '.'));
        total += price * quantity;
    });

    // Zaktualizuj wyświetlaną sumę
    const totalElement = document.querySelector('.cart-total');
    if (totalElement) {
        // Formatuj cenę w polskim formacie (przecinek zamiast kropki)
        totalElement.textContent = `Suma: ${total.toFixed(2).replace('.', ',')} zł`;
    }
}
