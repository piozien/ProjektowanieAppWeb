<?php
//----------------------------------------//
//          Store.php                      //
//----------------------------------------//
// Opis: Moduł sklepu internetowego       //
//   - Wyświetlanie produktów             //
//   - Zarządzanie koszykiem              //
//   - Obsługa zamówień                   //
//----------------------------------------//

class Store {
    /**
     * Wyświetla stronę sklepu z produktami
     */
    function StorePage() {
        global $conn;
        
        // Sprawdź czy dodano produkt do koszyka
        if (isset($_POST['add_produkt'])) {
            $this->AddToCart($_POST['add_produkt']);
        }

        echo '<div class="store-container">';
        
        // Wyświetlanie licznika koszyka
        echo '<div class="cart-summary">';
        if (isset($_SESSION['count'])) {
            echo '<a href="?idp=sklep2" class="cart-link">Koszyk <span class="cart-count">' . $_SESSION['count'] . '</span></a>';
        } else {
            echo '<a href="?idp=sklep2" class="cart-link">Koszyk <span class="cart-count">0</span></a>';
        }
        echo '</div>';

        // Menu kategorii
        echo '<div class="category-menu">';
        echo '<button class="category-menu-toggle">Kategorie</button>';
        echo '<div class="category-menu-content">';
        
        // Pobierz kategorie główne
        $query = "SELECT id, nazwa FROM category_list WHERE matka = 0 ORDER BY nazwa";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($category = $result->fetch_assoc()) {
                echo '<div class="category-item">';
                echo '<div class="category-header">';
                echo '<a href="#category-' . $category['id'] . '" class="category-link">' . htmlspecialchars($category['nazwa']) . '</a>';
                
                // Sprawdź czy kategoria ma podkategorie
                $checkSubquery = "SELECT COUNT(*) as count FROM category_list WHERE matka = ?";
                $checkStmt = $conn->prepare($checkSubquery);
                $checkStmt->bind_param("i", $category['id']);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                $hasSubcategories = $checkResult->fetch_assoc()['count'] > 0;
                $checkStmt->close();
                
                if ($hasSubcategories) {
                    echo '<span class="category-toggle">+</span>';
                }
                echo '</div>';
                
                if ($hasSubcategories) {
                    // Pobierz podkategorie
                    $subquery = "SELECT id, nazwa FROM category_list WHERE matka = ? ORDER BY nazwa";
                    $stmt = $conn->prepare($subquery);
                    $stmt->bind_param("i", $category['id']);
                    $stmt->execute();
                    $subresult = $stmt->get_result();
                    
                    if ($subresult && $subresult->num_rows > 0) {
                        echo '<div class="subcategory-list">';
                        while ($subcategory = $subresult->fetch_assoc()) {
                            echo '<a href="#subcategory-' . $subcategory['id'] . '" class="subcategory-link">' . 
                                 htmlspecialchars($subcategory['nazwa']) . '</a>';
                        }
                        echo '</div>';
                    }
                    $stmt->close();
                }
                
                echo '</div>'; // category-item
            }
        }
        
        echo '</div>'; // category-menu-content
        echo '</div>'; // category-menu

        // Pobieranie kategorii
        $query = "SELECT c1.*, c2.nazwa as parent_name 
                 FROM category_list c1 
                 LEFT JOIN category_list c2 ON c1.matka = c2.id 
                 ORDER BY c1.matka ASC, c1.nazwa ASC";
        $result = $conn->query($query);

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['matka'] == 0) {
                $categories[$row['id']] = [
                    'nazwa' => $row['nazwa'],
                    'subcategories' => []
                ];
            } else {
                $categories[$row['matka']]['subcategories'][] = [
                    'id' => $row['id'],
                    'nazwa' => $row['nazwa']
                ];
            }
        }

        // Wyświetlanie produktów według kategorii
        foreach ($categories as $parent_id => $category) {
            echo '<div class="category-section" id="category-' . $parent_id . '">';
            echo '<h2 class="category-title">' . htmlspecialchars($category['nazwa']) . '</h2>';
            
            foreach ($category['subcategories'] as $subcategory) {
                echo '<div class="subcategory-section" id="subcategory-' . $subcategory['id'] . '">';
                echo '<h3 class="subcategory-title">' . htmlspecialchars($subcategory['nazwa']) . '</h3>';
                
                // Pobieranie produktów dla podkategorii
                $stmt = $conn->prepare("SELECT id, tytul, opis, cena_netto, podatek_vat, ilosc_dostepnych, zdjecie 
                                      FROM product_list 
                                      WHERE kategoria = ? 
                                      ORDER BY tytul ASC");
                $stmt->bind_param("i", $subcategory['id']);
                $stmt->execute();
                $products = $stmt->get_result();

                echo '<div class="products-grid">';
                while ($product = $products->fetch_assoc()) {
                    $this->WyswietlProdukt($product);
                }
                echo '</div>'; // products-grid
                $stmt->close();
                
                echo '</div>'; // subcategory-section
            }
            echo '</div>'; // category-section
        }
        echo '</div>'; // store-container

        // Obsługa dodawania do koszyka
        if (isset($_POST['add_produkt'])) {
            $this->AddToCart((int)$_POST['add_produkt']);
        }
    }

    /**
     * Wyświetla pojedynczy produkt
     */
    private function WyswietlProdukt($product) {
        $cena_brutto = $product['cena_netto'] * (1 + $product['podatek_vat']/100);
        
        echo '<div class="product-card">';
        
        // Zdjęcie produktu
        echo '<div class="product-image">';
        if ($product['zdjecie'] && file_exists($product['zdjecie'])) {
            echo '<img src="' . $product['zdjecie'] . '" alt="' . htmlspecialchars($product['tytul']) . '">';
        } else {
            echo '<img src="images/products/no-image.png" alt="Brak zdjęcia">';
        }
        echo '</div>';
        
        // Informacje o produkcie
        echo '<div class="product-info">';
        echo '<h4 class="product-title">' . htmlspecialchars($product['tytul']) . '</h4>';
        echo '<p class="product-description">' . htmlspecialchars($product['opis']) . '</p>';
        echo '<div class="product-price">';
        echo '<span class="price-netto">' . number_format($product['cena_netto'], 2) . ' zł netto</span>';
        echo '<span class="price-brutto">' . number_format($cena_brutto, 2) . ' zł brutto</span>';
        echo '</div>';
        
        // Dostępność i przycisk dodawania do koszyka
        echo '<div class="product-actions">';
        if ($product['ilosc_dostepnych'] > 0) {
            $cart_quantity = isset($_SESSION['id_'.$product['id']]['ilosc']) ? $_SESSION['id_'.$product['id']]['ilosc'] : 0;
            $max_quantity = $product['ilosc_dostepnych'] - $cart_quantity;
            
            if ($max_quantity > 0) {
                echo '<form method="post" class="add-to-cart-form" onsubmit="return addToCart(this, event)">';
                echo '<input type="hidden" name="add_produkt" value="' . $product['id'] . '">';
                echo '<div class="quantity-selector">';
                echo '<select name="quantity" class="quantity-select">';
                for ($i = 1; $i <= min($max_quantity, 10); $i++) {
                    echo '<option value="' . $i . '">' . $i . '</option>';
                }
                echo '</select>';
                echo '</div>';
                echo '<button type="submit" class="add-to-cart-btn"><i class="fas fa-shopping-cart"></i> Dodaj do koszyka</button>';
                echo '</form>';
            } else {
                echo '<span class="out-of-stock">Maksymalna ilość w koszyku</span>';
            }
            echo '<span class="stock-info">Dostępne: ' . $product['ilosc_dostepnych'] . ' szt.</span>';
        } else {
            echo '<span class="out-of-stock">Produkt niedostępny</span>';
        }
        echo '</div>'; // product-actions
        
        echo '</div>'; // product-info
        echo '</div>'; // product-card
    }

    /**
     * Wyświetla zawartość koszyka
     * @description Odpowiada za:
     * - Wyświetlanie listy produktów w koszyku
     * - Obliczanie sumy zamówienia z uwzględnieniem VAT
     * - Obsługę przycisków zwiększania/zmniejszania ilości
     * - Wyświetlanie komunikatu o pustym koszyku
     * - Obsługę akcji koszyka (checkout, clear, add_one, remove_one)
     */
    function ShowCart() {
        global $conn;
        
        // Kontener główny koszyka
        echo '<div class="cart-container">';
        echo '<h2 class="cart-title">Koszyk</h2>';
        echo '<a href="?idp=sklep" class="back-to-store">Powrót do sklepu</a>';

        // Inicjalizacja zmiennych
        $total = 0;
        $has_items = false;

        // Pobierz ID produktów z koszyka z sesji
        // Filtruje klucze sesji zaczynające się od 'id_' i wyciąga z nich ID produktów
        $cart_ids = array_filter(array_map(function($key) {
            return substr($key, 3);
        }, array_filter(array_keys($_SESSION), function($key) {
            return strpos($key, 'id_') === 0;
        })));

        // Jeśli są produkty w koszyku
        if (!empty($cart_ids)) {
            // Pobierz szczegóły produktów z bazy danych
            $query = "SELECT id, tytul, cena_netto, podatek_vat, ilosc_dostepnych, zdjecie 
                     FROM product_list 
                     WHERE id IN (" . implode(',', $cart_ids) . ")";

            $result = $conn->query($query);

            // Jeśli znaleziono produkty
            if ($result && $result->num_rows > 0) {
                echo '<div class="cart-items">';
                while ($product = $result->fetch_assoc()) {
                    // Sprawdź czy produkt jest nadal w koszyku (sesji)
                    if (isset($_SESSION['id_'.$product['id']]['ilosc'])) {
                        $has_items = true;
                        $quantity = $_SESSION['id_'.$product['id']]['ilosc'];
                        // Oblicz cenę całkowitą dla produktu (z VAT * ilość)
                        $item_total = ($product['cena_netto'] * (1 + $product['podatek_vat']/100)) * $quantity;
                        $total += $item_total;

                        // Wyświetl produkt w koszyku
                        echo '<div class="cart-item">';
                        
                        // Zdjęcie produktu
                        echo '<div class="cart-item-image">';
                        if ($product['zdjecie'] && file_exists($product['zdjecie'])) {
                            echo '<img src="' . $product['zdjecie'] . '" alt="' . htmlspecialchars($product['tytul']) . '">';
                        } else {
                            echo '<img src="images/products/no-image.png" alt="Brak zdjęcia">';
                        }
                        echo '</div>';

                        // Szczegóły produktu
                        echo '<div class="cart-item-details">';
                        echo '<h4 class="cart-item-title">' . htmlspecialchars($product['tytul']) . '</h4>';
                        echo '<div class="cart-item-price">';
                        echo '<span>Cena: ' . number_format($item_total, 2) . ' zł</span>';
                        echo '</div>';
                        
                        // Przyciski do zmiany ilości
                        echo '<div class="cart-item-quantity">';
                        echo '<span>Ilość: <span class="quantity-value">' . $quantity . '</span></span>';
                        // Przycisk + tylko jeśli jest dostępna większa ilość
                        if ($quantity < $product['ilosc_dostepnych']) {
                            echo '<button type="button" onclick="updateCartQuantity(' . $product['id'] . ', \'add\')" class="quantity-btn add">+</button>';
                        }
                        echo '<button type="button" onclick="updateCartQuantity(' . $product['id'] . ', \'remove\')" class="quantity-btn remove">-</button>';
                        echo '</div>';
                        
                        echo '</div>'; // cart-item-details
                        echo '</div>'; // cart-item
                    }
                }
                echo '</div>'; // cart-items

                // Wyświetl podsumowanie i przyciski akcji
                if ($has_items) {
                    echo '<div class="cart-summary">';
                    echo '<div class="cart-total">Suma: ' . number_format($total, 2) . ' zł</div>';
                    echo '<div class="cart-actions">';
                    echo '<a href="?idp=sklep2&checkout=1" class="checkout-btn">Złóż zamówienie</a>';
                    echo '<a href="?idp=sklep2&clear=1" class="clear-cart-btn">Wyczyść koszyk</a>';
                    echo '</div>';
                    echo '</div>';
                }
            }
        } else {
            // Wyświetl komunikat o pustym koszyku
            echo '<div class="empty-cart">';
            echo '<i class="fas fa-shopping-cart"></i>';
            echo '<p>Twój koszyk jest pusty</p>';
            echo '<a href="?idp=sklep" class="continue-shopping">Kontynuuj zakupy</a>';
            echo '</div>';
        }

        echo '</div>'; // cart-container

        // Obsługa akcji koszyka
        if (isset($_GET['checkout'])) {
            $this->CheckoutCart();
        }
        if (isset($_GET['clear'])) {
            $this->ClearCart();
        }
        if (isset($_GET['add_one'])) {
            $this->AddOne((int)$_GET['add_one']);
        }
        if (isset($_GET['remove_one'])) {
            $this->RemoveOne((int)$_GET['remove_one']);
        }
    }

    /**
     * Przekierowanie do innej strony
     */
    function RedirectTo($page) {
        echo '<script>window.location.href = "index.php?idp=' . $page . '";</script>';
        exit();
    }

    /**
     * Dodaje produkt do koszyka
     */
    public function AddToCart($id_produktu) {
        global $conn;
        
        // Pobierz ilość z formularza lub ustaw domyślnie 1
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        // Sprawdź czy produkt istnieje i jest dostępny
        $stmt = $conn->prepare("SELECT id, ilosc_dostepnych, tytul, cena_netto, podatek_vat FROM product_list WHERE id = ?");
        $stmt->bind_param("i", $id_produktu);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            
            // Inicjalizuj sesję jeśli nie istnieje
            if (!isset($_SESSION['koszyk'])) {
                $_SESSION['koszyk'] = [];
                $_SESSION['count'] = 0;
            }
            
            // Sprawdź aktualną ilość w koszyku
            $current_quantity = isset($_SESSION['id_'.$id_produktu]['ilosc']) ? $_SESSION['id_'.$id_produktu]['ilosc'] : 0;
            
            // Sprawdź czy nie przekraczamy dostępnej ilości
            if (($current_quantity + $quantity) <= $product['ilosc_dostepnych']) {
                // Jeśli produkt już jest w koszyku, zwiększ ilość
                if (isset($_SESSION['id_'.$id_produktu])) {
                    $_SESSION['id_'.$id_produktu]['ilosc'] += $quantity;
                } else {
                    // Dodaj nowy produkt do koszyka
                    $_SESSION['id_'.$id_produktu] = [
                        'id' => $id_produktu,
                        'tytul' => $product['tytul'],
                        'ilosc' => $quantity,
                        'cena_netto' => $product['cena_netto'],
                        'podatek_vat' => $product['podatek_vat']
                    ];
                }
                
                // Aktualizuj licznik produktów w koszyku
                $_SESSION['count'] = isset($_SESSION['count']) ? $_SESSION['count'] + $quantity : $quantity;
            }
        }
        $stmt->close();
        
        // Przekieruj z powrotem do sklepu
        header("Location: ?idp=sklep");
        exit();
    }

    /**
     * Dodaje jedną sztukę produktu w koszyku
     */
    function AddOne($id_produktu) {
        $this->AddToCart($id_produktu);
        $this->RedirectTo('sklep2');
    }

    /**
     * Usuwa jedną sztukę produktu z koszyka
     */
    function RemoveOne($id_produktu) {
        $id_produktu = (int)$id_produktu;
        
        if (isset($_SESSION['id_'.$id_produktu])) {
            if ($_SESSION['id_'.$id_produktu]['ilosc'] == 1) {
                unset($_SESSION['id_'.$id_produktu]);
            } else {
                $_SESSION['id_'.$id_produktu]['ilosc']--;
            }
            
            $_SESSION['count'] = $_SESSION['count'] - 1;
            if ($_SESSION['count'] <= 0) {
                unset($_SESSION['count']);
            }
        }
        
        $this->RedirectTo('sklep2');
    }

    /**
     * Czyści koszyk
     */
    function ClearCart() {
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, 'id_') === 0) {
                unset($_SESSION[$key]);
            }
        }
        unset($_SESSION['count']);
        
        $this->RedirectTo('sklep2');
    }

    /**
     * Finalizuje zamówienie
     */
    function CheckoutCart()
    {
        global $conn;
        
        if (!isset($_SESSION['count']) || $_SESSION['count'] <= 0) {
            $this->RedirectTo('sklep2');
            return;
        }

        try {
            $conn->begin_transaction();
            
            // Obliczanie całkowitej wartości zamówienia
            $total_price = 0;
            foreach ($_SESSION as $key => $value) {
                if (strpos($key, 'id_') === 0) {
                    $id_produktu = (int)substr($key, 3);
                    $ilosc = (int)$value['ilosc'];
                    
                    // Pobierz cenę produktu
                    $stmt = $conn->prepare("SELECT cena_netto, podatek_vat FROM product_list WHERE id = ?");
                    $stmt->bind_param("i", $id_produktu);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $product = $result->fetch_assoc();
                    $stmt->close();
                    
                    // Oblicz cenę brutto
                    $cena_brutto = $product['cena_netto'] * (1 + $product['podatek_vat']/100);
                    $total_price += $cena_brutto * $ilosc;
                }
            }
            
            // Tworzenie nowego zamówienia
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            $stmt = $conn->prepare("INSERT INTO orders (user_id, status, total_price) VALUES (?, 'nowe', ?)");
            $stmt->bind_param("id", $user_id, $total_price);
            $stmt->execute();
            $order_id = $conn->insert_id;
            $stmt->close();
            
            // Dodawanie produktów do zamówienia
            foreach ($_SESSION as $key => $value) {
                if (strpos($key, 'id_') === 0) {
                    $id_produktu = (int)substr($key, 3);
                    $ilosc = (int)$value['ilosc'];
                    
                    // Pobierz cenę produktu
                    $stmt = $conn->prepare("SELECT cena_netto, podatek_vat, ilosc_dostepnych FROM product_list WHERE id = ?");
                    $stmt->bind_param("i", $id_produktu);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $product = $result->fetch_assoc();
                    $stmt->close();
                    
                    // Oblicz cenę brutto
                    $cena_brutto = $product['cena_netto'] * (1 + $product['podatek_vat']/100);
                    
                    // Dodaj szczegóły zamówienia
                    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiid", $order_id, $id_produktu, $ilosc, $cena_brutto);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Aktualizacja stanu magazynowego
                    $stmt = $conn->prepare("UPDATE product_list 
                                          SET ilosc_dostepnych = ilosc_dostepnych - ?
                                          WHERE id = ?");
                    $stmt->bind_param("ii", $ilosc, $id_produktu);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            
            $conn->commit();
            
            // Wyczyść koszyk
            foreach ($_SESSION as $key => $value) {
                if (strpos($key, 'id_') === 0) {
                    unset($_SESSION[$key]);
                }
            }
            unset($_SESSION['count']);
            
            // Pokaż potwierdzenie
            echo '<div class="success-message">';
            echo '<h3>Zamówienie zostało złożone!</h3>';
            echo '<p>Numer zamówienia: ' . $order_id . '</p>';
            echo '<p>Wartość zamówienia: ' . number_format($total_price, 2) . ' zł</p>';
            echo '<a href="?idp=sklep" class="continue-shopping">Kontynuuj zakupy</a>';
            echo '</div>';
            
        } catch (Exception $e) {
            $conn->rollback();
            echo '<div class="error-message">Wystąpił błąd podczas składania zamówienia: ' . $e->getMessage() . '</div>';
            echo '<a href="?idp=sklep2" class="back-to-cart">Wróć do koszyka</a>';
        }
    }
}
?>
