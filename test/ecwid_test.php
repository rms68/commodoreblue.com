<?php
// Ecwid API Credentials
$apiToken = 'secret_CgiHaSu3pRmNE4PzpfaPufuEu9JX6Dx8';
$storeId = '108400041';

// Fetch Product Data
$url = "https://app.ecwid.com/api/v3/{$storeId}/products?limit=100";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer {$apiToken}"]);
$response = curl_exec($ch);
curl_close($ch);

$products = json_decode($response, true)['items'] ?? [];

// Handle item details view
$itemId = isset($_GET['item_id']) ? $_GET['item_id'] : null;
$selectedProduct = null;
if ($itemId) {
    foreach ($products as $product) {
        if ($product['id'] == $itemId) {
            $selectedProduct = $product;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product List</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #222; color: #ddd; }
        .product { display: inline-block; width: 200px; margin: 10px; text-align: center; border: 1px solid #555; background: #333; border-radius: 8px; padding: 10px; }
        .product img { max-width: 100px; }
        a { color: #f90; text-decoration: none; }
        h2, h3 { margin: 5px 0; color: #f90; }
        .details { background: #333; padding: 20px; border: 1px solid #555; border-radius: 8px; }
        button { padding: 10px 15px; background-color: #f90; color: #222; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #fff; color: #f90; }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Product List</h1>

    <?php if ($selectedProduct): ?>
        <!-- Product Details Section -->
        <div class="details" style="text-align: center;">
            <h2><?php echo htmlspecialchars($selectedProduct['name']); ?></h2>
            <img src="<?php echo htmlspecialchars($selectedProduct['imageUrl']); ?>" alt="Product Image">
            <p><strong>Price:</strong> $<?php echo htmlspecialchars($selectedProduct['price']); ?></p>
            <p><strong>Description:</strong> <?php echo strip_tags($selectedProduct['description']); ?></p>
            <!-- Add to Cart Link -->
            <a href="https://store.ecwid.com/store/<?php echo $storeId; ?>/cart?add=<?php echo $selectedProduct['id']; ?>" target="_blank">
                <button>Buy Now</button>
            </a>
            <br><br>
            <a href="?" style="color: #fff;">&larr; Back to Product List</a>
        </div>
    <?php else: ?>
        <!-- Product List Section -->
        <div style="text-align: center;">
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <img src="<?php echo htmlspecialchars($product['thumbnailUrl']); ?>" alt="Product Image">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p>Price: $<?php echo htmlspecialchars($product['price']); ?></p>
                    <a href="?item_id=<?php echo $product['id']; ?>">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>
