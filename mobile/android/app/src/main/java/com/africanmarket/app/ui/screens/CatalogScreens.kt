package com.africanmarket.app.ui.screens

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.Card
import androidx.compose.material3.FilterChip
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavHostController
import coil.compose.AsyncImage
import com.africanmarket.app.data.model.Category
import com.africanmarket.app.data.model.Product
import com.africanmarket.app.data.repository.ApiResult
import com.africanmarket.app.data.repository.ProductRepository
import com.africanmarket.app.ui.components.CenteredProgress
import com.africanmarket.app.ui.components.ErrorText
import com.africanmarket.app.ui.components.LoadingRow
import com.africanmarket.app.ui.components.ScreenHeader
import com.africanmarket.app.ui.navigation.Screen
import com.africanmarket.app.ui.viewmodel.CartViewModel
import com.africanmarket.app.ui.viewmodel.ProductViewModel
import com.africanmarket.app.util.formatPrice
import com.africanmarket.app.util.resolveImageUrl
import androidx.compose.runtime.collectAsState

@Composable
fun HomeScreen(navController: NavHostController) {
    val vm: ProductViewModel = viewModel()
    val products by vm.products.collectAsState()
    val state by vm.state.collectAsState()
    var search by remember { mutableStateOf("") }
    var categories by remember { mutableStateOf<List<Category>>(emptyList()) }
    var selectedCategory by remember { mutableStateOf<Int?>(null) }

    LaunchedEffect(Unit) {
        vm.refresh()
        when (val r = ProductRepository.categories(null)) {
            is ApiResult.Success -> categories = r.data
            else -> {}
        }
    }

    Column(Modifier.fillMaxSize()) {
        ScreenHeader("Shop")
        OutlinedTextField(
            value = search,
            onValueChange = {
                search = it
                vm.search = it.takeIf { s -> s.isNotBlank() }
                vm.refresh()
            },
            label = { Text("Search products") },
            singleLine = true,
            modifier = Modifier.fillMaxWidth().padding(horizontal = 16.dp),
        )
        Spacer(Modifier.height(8.dp))
        LazyRow(Modifier.padding(horizontal = 8.dp)) {
            items(categories) { cat ->
                FilterChip(
                    selected = selectedCategory == cat.id,
                    onClick = {
                        selectedCategory = if (selectedCategory == cat.id) null else cat.id
                        vm.categoryId = selectedCategory
                        vm.refresh()
                    },
                    label = { Text(cat.name) },
                    modifier = Modifier.padding(horizontal = 4.dp),
                )
            }
        }
        Spacer(Modifier.height(8.dp))
        ErrorText(state.error)
        LazyVerticalGrid(
            columns = GridCells.Adaptive(minSize = 160.dp),
            modifier = Modifier.fillMaxSize().padding(horizontal = 8.dp),
            contentPadding = PaddingValues(bottom = 16.dp),
        ) {
            items(products, key = { it.id }) { product ->
                ProductCard(product) { navController.navigate(Screen.ProductDetail.create(product.id)) }
            }
            if (state.loading) {
                item { LoadingRow() }
            }
        }
    }
}

@Composable
fun ProductCard(product: Product, onClick: () -> Unit) {
    Card(
        onClick = onClick,
        modifier = Modifier.padding(8.dp).fillMaxWidth(),
    ) {
        Column(Modifier.padding(8.dp)) {
            AsyncImage(
                model = resolveImageUrl(product.featuredImage),
                contentDescription = product.name,
                modifier = Modifier.fillMaxWidth().height(120.dp),
                contentScale = ContentScale.Crop,
            )
            Spacer(Modifier.height(8.dp))
            Text(product.name, style = MaterialTheme.typography.bodyMedium, maxLines = 2)
            Text(
                formatPrice(product.price),
                style = MaterialTheme.typography.titleMedium,
                color = MaterialTheme.colorScheme.primary,
            )
        }
    }
}

@Composable
fun ProductDetailScreen(navController: NavHostController, id: Int) {
    var product by remember { mutableStateOf<Product?>(null) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }
    val cart: CartViewModel = viewModel()
    val cartState by cart.state.collectAsState()

    LaunchedEffect(Unit) {
        when (val r = ProductRepository.product(id)) {
            is ApiResult.Success -> product = r.data
            is ApiResult.Error -> error = r.message
        }
        loading = false
    }

    if (loading) {
        CenteredProgress()
        return
    }

    val p = product
    if (p == null) {
        Column {
            ScreenHeader("Product", onBack = { navController.popBackStack() })
            ErrorText(error ?: "Product not found")
        }
        return
    }

    Column(Modifier.fillMaxSize().verticalScroll(rememberScrollState())) {
        ScreenHeader(p.name, onBack = { navController.popBackStack() })
        AsyncImage(
            model = resolveImageUrl(p.featuredImage),
            contentDescription = p.name,
            modifier = Modifier.fillMaxWidth().height(220.dp),
            contentScale = ContentScale.Crop,
        )
        Column(Modifier.padding(16.dp)) {
            Text(p.name, style = MaterialTheme.typography.titleLarge)
            Text(
                formatPrice(p.price),
                style = MaterialTheme.typography.headlineSmall,
                color = MaterialTheme.colorScheme.primary,
            )
            p.comparePrice?.let {
                Text("Was ${formatPrice(it)}", style = MaterialTheme.typography.bodySmall)
            }
            Spacer(Modifier.height(8.dp))
            (p.shortDescription ?: p.description)?.let { Text(it) }
            Spacer(Modifier.height(16.dp))
            ErrorText(cartState.error)
            Row(
                Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.spacedBy(8.dp),
            ) {
                androidx.compose.material3.Button(
                    onClick = { cart.add(p.id) },
                    modifier = Modifier.weight(1f),
                    enabled = !cartState.loading,
                ) { Text("Add to cart") }
            }
        }
    }
}
