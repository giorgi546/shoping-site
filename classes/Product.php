<?php
require_once 'includes/config.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$product_id) {
    header('Location: shop.php');
    exit;
}

// Initialize classes
$product = new Product($db);
$current_product = $product->getById($product_id);

if (!$current_product) {
    header('Location: shop.php');
    exit;
}

// Get related products
$related_products = $product->getRelated($product_id, $current_product['category_id']);

// Parse gallery images
$gallery_images = [];
if ($current_product['gallery_images']) {
    $gallery_images = json_decode($current_product['gallery_images'], true) ?: [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($current_product['name']); ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($current_product['short_description']); ?>">
    
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        .product-header {
            background: #F8FAFC;
            padding: 2rem 0;
            margin-top: 80px;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #6B7280;
        }
        
        .breadcrumb a {
            color: #3B82F6;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .product-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            padding: 3rem 0;
        }
        
        .product-gallery {
            position: sticky;
            top: 120px;
            height: fit-content;
        }
        
        .main-image {
            width: 100%;
            height: 500px;
            background: white;
            border-radius: 12px;
            margin-bottom: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            position: relative;
        }
        
        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .image-zoom {
            cursor: zoom-in;
        }
        
        .image-zoom:hover img {
            transform: scale(1.1);
        }
        
        .image-thumbnails {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding: 0.5rem 0;
        }
        
        .thumbnail {
            flex-shrink: 0;
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s ease;
        }
        
        .thumbnail.active {
            border-color: #3B82F6;
        }
        
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-info {
            padding: 1rem 0;
        }
        
        .product-category {
            color: #6B7280;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }
        
        .product-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .product-price {
            margin-bottom: 1.5rem;
        }
        
        .price-current,
        .price-sale {
            font-size: 2rem;
            font-weight: 700;
            color: #1F2937;
        }
        
        .price-original {
            font-size: 1.5rem;
            color: #9CA3AF;
            text-decoration: line-through;
            margin-left: 0.5rem;
        }
        
        .price-savings {
            display: inline-block;
            background: #FEE2E2;
            color: #DC2626;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-left: 1rem;
        }
        
        .product-description {
            color: #4B5563;
            line-height: 1.7;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .product-features {
            margin-bottom: 2rem;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            color: #4B5563;
        }
        
        .feature-list i {
            color: #10B981;
            font-size: 0.9rem;
        }
        
        .product-options {
            margin-bottom: 2rem;
        }
        
        .option-group {
            margin-bottom: 1.5rem;
        }
        
        .option-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .qty-controls {
            display: flex;
            align-items: center;
            border: 2px solid #E5E7EB;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .qty-btn {
            width: 45px;
            height: 45px;
            border: none;
            background: #F9FAFB;
            cursor: pointer;
            font-size: 1.2rem;
            transition: background 0.3s ease;
        }
        
        .qty-btn:hover {
            background: #E5E7EB;
        }
        
        .qty-input {
            width: 60px;
            height: 45px;
            border: none;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .stock-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #10B981;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }
        
        .stock-info.low-stock {
            color: #F59E0B;
        }
        
        .stock-info.out-of-stock {
            color: #EF4444;
        }
        
        .product-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .add-to-cart-btn {
            flex: 2;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .wishlist-btn {
            flex: 1;
            background: transparent;
            border: 2px solid #E5E7EB;
            color: #6B7280;
            padding: 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .wishlist-btn:hover {
            border-color: #EF4444;
            color: #EF4444;
        }
        
        .product-meta {
            border-top: 1px solid #E5E7EB;
            padding-top: 2rem;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            color: #6B7280;
            font-size: 0.95rem;
        }
        
        .meta-item strong {
            color: #374151;
        }
        
        .related-products {
            padding: 4rem 0;
            background: #F8FAFC;
        }
        
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1F2937;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .zoom-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
        }
        
        .zoom-image {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
        }
        
        .zoom-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 2rem;
            cursor: pointer;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 1024px) {
            .product-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .product-gallery {
                position: static;
            }
            
            .main-image {
                height: 400px;
            }
        }
        
        @media (max-width: 768px) {
            .product-title {
                font-size: 2rem;
            }
            
            .product-actions {
                flex-direction: column;
            }
            
            .quantity-selector {
                justify-content: space-between;
            }
            
            .related-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>

    <!-- Product Header -->
    <section class="product-header">
        <div class="container">
            <nav class="breadcrumb">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <i class="fas fa-chevron-right"></i>
                <a href="shop.php">Shop</a>
                <?php if ($current_product['category_name']): ?>
                    <i class="fas fa-chevron-right"></i>
                    <a href="shop.php?category=<?php echo urlencode($current_product['category_name']); ?>">
                        <?php echo htmlspecialchars($current_product['category_name']); ?>
                    </a>
                <?php endif; ?>
                <i class="fas fa-chevron-right"></i>
                <span><?php echo htmlspecialchars($current_product['name']); ?></span>
            </nav>
        </div>
    </section>

    <!-- Product Details -->
    <section class="product-section">
        <div class="container">
            <div class="product-container">
                <!-- Product Gallery -->
                <div class="product-gallery">
                    <div class="main-image image-zoom" onclick="openZoom('<?php echo $current_product['main_image'] ? 'uploads/products/' . $current_product['main_image'] : 'images/placeholder.jpg'; ?>')">
                        <img src="<?php echo $current_product['main_image'] ? 'uploads/products/' . $current_product['main_image'] : 'images/placeholder.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($current_product['name']); ?>" 
                             id="mainImage">
                    </div>
                    
                    <?php if (!empty($gallery_images) || $current_product['main_image']): ?>
                        <div class="image-thumbnails">
                            <!-- Main image thumbnail -->
                            <div class="thumbnail active" onclick="changeMainImage('<?php echo $current_product['main_image'] ? 'uploads/products/' . $current_product['main_image'] : 'images/placeholder.jpg'; ?>')">
                                <img src="<?php echo $current_product['main_image'] ? 'uploads/products/' . $current_product['main_image'] : 'images/placeholder.jpg'; ?>" 
                                     alt="Main image">
                            </div>
                            
                            <!-- Gallery thumbnails -->
                            <?php foreach ($gallery_images as $image): ?>
                                <div class="thumbnail" onclick="changeMainImage('uploads/products/<?php echo $image; ?>')">
                                    <img src="uploads/products/<?php echo $image; ?>" alt="Gallery image">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <div class="product-category">
                        <?php echo htmlspecialchars($current_product['category_name'] ?? 'Uncategorized'); ?>
                    </div>
                    
                    <h1 class="product-title"><?php echo htmlspecialchars($current_product['name']); ?></h1>
                    
                    <div class="product-price">
                        <?php if ($current_product['sale_price']): ?>
                            <span class="price-sale">$<?php echo number_format($current_product['sale_price'], 2); ?></span>
                            <span class="price-original">$<?php echo number_format($current_product['price'], 2); ?></span>
                            <?php 
                            $savings = $current_product['price'] - $current_product['sale_price'];
                            $savings_percent = round(($savings / $current_product['price']) * 100);
                            ?>
                            <span class="price-savings">Save <?php echo $savings_percent; ?>%</span>
                        <?php else: ?>
                            <span class="price-current">$<?php echo number_format($current_product['price'], 2); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Stock Status -->
                    <div class="stock-info <?php echo $current_product['stock_quantity'] <= 0 ? 'out-of-stock' : ($current_product['stock_quantity'] <= 5 ? 'low-stock' : ''); ?>">
                        <i class="fas fa-<?php echo $current_product['stock_quantity'] <= 0 ? 'times-circle' : ($current_product['stock_quantity'] <= 5 ? 'exclamation-triangle' : 'check-circle'); ?>"></i>
                        <?php if ($current_product['stock_quantity'] <= 0): ?>
                            Out of Stock
                        <?php elseif ($current_product['stock_quantity'] <= 5): ?>
                            Only <?php echo $current_product['stock_quantity']; ?> left in stock
                        <?php else: ?>
                            In Stock (<?php echo $current_product['stock_quantity']; ?> available)
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-description">
                        <?php echo nl2br(htmlspecialchars($current_product['description'])); ?>
                    </div>
                    
                    <?php if ($current_product['stock_quantity'] > 0): ?>
                        <!-- Quantity Selector -->
                        <div class="quantity-selector">
                            <label class="option-label">Quantity:</label>
                            <div class="qty-controls">
                                <button type="button" class="qty-btn qty-decrease">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="qty-input" value="1" min="1" max="<?php echo $current_product['stock_quantity']; ?>">
                                <button type="button" class="qty-btn qty-increase">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Product Actions -->
                        <div class="product-actions">
                            <button class="btn btn-primary add-to-cart-btn" data-id="<?php echo $current_product['id']; ?>">
                                <i class="fas fa-shopping-cart"></i>
                                Add to Cart
                            </button>
                            <button class="wishlist-btn" data-id="<?php echo $current_product['id']; ?>">
                                <i class="far fa-heart"></i>
                                Wishlist
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="product-actions">
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-times"></i>
                                Out of Stock
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Product Meta -->
                    <div class="product-meta">
                        <?php if ($current_product['sku']): ?>
                            <div class="meta-item">
                                <strong>SKU:</strong> <?php echo htmlspecialchars($current_product['sku']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($current_product['weight']): ?>
                            <div class="meta-item">
                                <strong>Weight:</strong> <?php echo htmlspecialchars($current_product['weight']); ?> lbs
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($current_product['dimensions']): ?>
                            <div class="meta-item">
                                <strong>Dimensions:</strong> <?php echo htmlspecialchars($current_product['dimensions']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="meta-item">
                            <strong>Category:</strong> <?php echo htmlspecialchars($current_product['category_name'] ?? 'Uncategorized'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <section class="related-products">
            <div class="container">
                <h2 class="section-title">Related Products</h2>
                <div class="related-grid">
                    <?php foreach ($related_products as $related): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo $related['main_image'] ? 'uploads/products/' . $related['main_image'] : 'images/placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($related['name']); ?>">
                                <div class="product-overlay">
                                    <button class="btn-icon quick-view" data-id="<?php echo $related['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon add-to-cart" data-id="<?php echo $related['id']; ?>">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name">
                                    <a href="product.php?id=<?php echo $related['id']; ?>">
                                        <?php echo htmlspecialchars($related['name']); ?>
                                    </a>
                                </h3>
                                <div class="product-price">
                                    <?php if ($related['sale_price']): ?>
                                        <span class="price-sale">$<?php echo number_format($related['sale_price'], 2); ?></span>
                                        <span class="price-original">$<?php echo number_format($related['price'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="price-current">$<?php echo number_format($related['price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Image Zoom Overlay -->
    <div class="zoom-overlay" id="zoomOverlay" onclick="closeZoom()">
        <span class="zoom-close">&times;</span>
        <img class="zoom-image" id="zoomImage" src="" alt="Zoomed image">
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="js/main.js"></script>
    
    <script>
        $(document).ready(function() {
            // Quantity controls
            $('.qty-increase').click(function() {
                const input = $('.qty-input');
                const max = parseInt(input.attr('max'));
                const current = parseInt(input.val());
                
                if (current < max) {
                    input.val(current + 1);
                }
            });
            
            $('.qty-decrease').click(function() {
                const input = $('.qty-input');
                const min = parseInt(input.attr('min'));
                const current = parseInt(input.val());
                
                if (current > min) {
                    input.val(current - 1);
                }
            });
            
            // Add to cart with quantity
            $('.add-to-cart-btn').click(function() {
                const productId = $(this).data('id');
                const quantity = $('.qty-input').val();
                
                addToCart(productId, quantity);
            });
            
            // Wishlist functionality
            $('.wishlist-btn').click(function() {
                const productId = $(this).data('id');
                const button = $(this);
                
                $.ajax({
                    url: 'ajax/toggle_wishlist.php',
                    method: 'POST',
                    data: { product_id: productId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            if (response.added) {
                                button.html('<i class="fas fa-heart"></i> In Wishlist');
                                button.css('color', '#EF4444');
                            } else {
                                button.html('<i class="far fa-heart"></i> Wishlist');
                                button.css('color', '#6B7280');
                            }
                            showAlert(response.message, 'success');
                        } else {
                            showAlert(response.message || 'Failed to update wishlist', 'error');
                        }
                    },
                    error: function() {
                        showAlert('Please login to use wishlist', 'error');
                    }
                });
            });
        });
        
        // Image gallery functions
        function changeMainImage(imageSrc) {
            $('#mainImage').attr('src', imageSrc);
            
            // Update active thumbnail
            $('.thumbnail').removeClass('active');
            event.currentTarget.classList.add('active');
        }
        
        function openZoom(imageSrc) {
            $('#zoomImage').attr('src', imageSrc);
            $('#zoomOverlay').fadeIn(300);
        }
        
        function closeZoom() {
            $('#zoomOverlay').fadeOut(300);
        }
        
        // Close zoom with Escape key
        $(document).keyup(function(e) {
            if (e.keyCode === 27) { // Escape key
                closeZoom();
            }
        });
    </script>
</body>
</html>