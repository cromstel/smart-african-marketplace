package com.africanmarket.app.ui.screens

import android.content.Intent
import android.net.Uri
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.Edit
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.RadioButton
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavHostController
import coil.compose.AsyncImage
import com.africanmarket.app.data.model.Address
import com.africanmarket.app.data.model.AddressRequest
import com.africanmarket.app.data.model.CartItem
import com.africanmarket.app.ui.components.CenteredProgress
import com.africanmarket.app.ui.components.ErrorText
import com.africanmarket.app.ui.components.ScreenHeader
import com.africanmarket.app.ui.navigation.Screen
import com.africanmarket.app.ui.viewmodel.AddressViewModel
import com.africanmarket.app.ui.viewmodel.CartViewModel
import com.africanmarket.app.ui.viewmodel.CheckoutOutcome
import com.africanmarket.app.ui.viewmodel.CheckoutViewModel
import com.africanmarket.app.util.formatPrice
import com.africanmarket.app.util.resolveImageUrl
import androidx.compose.runtime.collectAsState

@Composable
fun CartScreen(navController: NavHostController) {
    val cart: CartViewModel = viewModel()
    val cartData by cart.cart.collectAsState()
    val state by cart.state.collectAsState()

    LaunchedEffect(Unit) { cart.load() }

    Column(Modifier.fillMaxSize()) {
        ScreenHeader("Cart")
        ErrorText(state.error)
        val c = cartData
        when {
            c == null -> CenteredProgress()
            c.items.isNullOrEmpty() -> Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                Text("Your cart is empty")
            }
            else -> {
                LazyColumn(Modifier.weight(1f).padding(horizontal = 8.dp)) {
                    items(c.items ?: emptyList()) { item -> CartItemRow(item) }
                }
                Column(Modifier.padding(16.dp)) {
                    Text("Total: ${formatPrice(c.total)}", style = MaterialTheme.typography.titleMedium)
                    Spacer(Modifier.height(8.dp))
                    Button(
                        onClick = { navController.navigate(Screen.Checkout.route) },
                        modifier = Modifier.fillMaxWidth(),
                    ) { Text("Checkout") }
                }
            }
        }
    }
}

@Composable
fun CartItemRow(item: CartItem) {
    Card(Modifier.fillMaxWidth().padding(8.dp)) {
        Row(Modifier.padding(8.dp), verticalAlignment = Alignment.CenterVertically) {
            AsyncImage(
                model = resolveImageUrl(item.product?.featuredImage),
                contentDescription = null,
                modifier = Modifier.size(56.dp).clip(RoundedCornerShape(8.dp)),
            )
            Spacer(Modifier.width(8.dp))
            Column(Modifier.weight(1f)) {
                Text(item.product?.name ?: "Item #${item.productId}")
                Text("Qty: ${item.quantity}  ${formatPrice(item.subtotal)}")
            }
        }
    }
}

@Composable
fun CheckoutScreen(navController: NavHostController) {
    val checkout: CheckoutViewModel = viewModel()
    val addresses by checkout.addresses.collectAsState()
    val preview by checkout.preview.collectAsState()
    val result by checkout.result.collectAsState()
    val state by checkout.state.collectAsState()

    var selectedAddressId by remember { mutableStateOf<Int?>(null) }
    var paymentMethod by remember { mutableStateOf("paystack") }
    var phone by remember { mutableStateOf("") }
    var provider by remember { mutableStateOf("mtn") }

    LaunchedEffect(Unit) { checkout.loadAddresses() }

    LaunchedEffect(selectedAddressId) {
        selectedAddressId?.let { checkout.preview(it) }
    }

    LaunchedEffect(result) {
        when (val r = result) {
            is CheckoutOutcome.OpenPayment -> {
                r.url?.let { url ->
                    val intent = Intent(Intent.ACTION_VIEW, Uri.parse(url))
                    navController.context.startActivity(intent)
                }
                checkout.clearResult()
                navController.navigate(Screen.Orders.route) { popUpTo(Screen.Checkout.route) { inclusive = true } }
            }
            is CheckoutOutcome.OrderPlaced -> {
                checkout.clearResult()
                navController.navigate(Screen.Orders.route) { popUpTo(Screen.Checkout.route) { inclusive = true } }
            }
            null -> {}
        }
    }

    val methods = listOf("paystack", "stripe", "mobile_money", "cash_on_delivery")

    Column(Modifier.fillMaxSize()) {
        ScreenHeader("Checkout", onBack = { navController.popBackStack() })
        ErrorText(state.error)
        LazyColumn(Modifier.weight(1f).padding(16.dp)) {
            item {
                Text("Shipping address", style = MaterialTheme.typography.titleMedium)
                Spacer(Modifier.height(8.dp))
                if (addresses.isEmpty()) {
                    Text("No addresses saved yet.")
                    Button(onClick = { navController.navigate(Screen.AddressForm.create(null)) }) {
                        Text("Add address")
                    }
                } else {
                    addresses.forEach { addr ->
                        Row(
                            Modifier.fillMaxWidth().clickable { selectedAddressId = addr.id },
                            verticalAlignment = Alignment.CenterVertically,
                        ) {
                            RadioButton(selected = selectedAddressId == addr.id, onClick = { selectedAddressId = addr.id })
                            Column(Modifier.padding(start = 8.dp)) {
                                Text(addr.fullName ?: "${addr.firstName ?: ""} ${addr.lastName ?: ""}".trim())
                                Text(addr.fullAddress ?: addr.addressLine1 ?: "", style = MaterialTheme.typography.bodySmall)
                            }
                        }
                    }
                }
                Spacer(Modifier.height(16.dp))
                Text("Payment method", style = MaterialTheme.typography.titleMedium)
                Spacer(Modifier.height(8.dp))
                methods.forEach { method ->
                    Row(
                        Modifier.fillMaxWidth().clickable { paymentMethod = method },
                        verticalAlignment = Alignment.CenterVertically,
                    ) {
                        RadioButton(selected = paymentMethod == method, onClick = { paymentMethod = method })
                        Text(method.replace("_", " ").replaceFirstChar { it.uppercase() })
                    }
                }
                if (paymentMethod == "mobile_money") {
                    Spacer(Modifier.height(8.dp))
                    OutlinedTextField(value = phone, onValueChange = { phone = it }, label = { Text("Phone number") }, singleLine = true, modifier = Modifier.fillMaxWidth())
                    OutlinedTextField(value = provider, onValueChange = { provider = it }, label = { Text("Provider (mtn / mpesa)") }, singleLine = true, modifier = Modifier.fillMaxWidth())
                }
                if (paymentMethod == "swish") {
                    Text("Swish is not supported.", color = MaterialTheme.colorScheme.error)
                }
                Spacer(Modifier.height(16.dp))
                preview?.let { p ->
                    Text("Subtotal: ${formatPrice(p.subtotal)}")
                    Text("Shipping: ${formatPrice(p.shipping)}")
                    Text("Tax: ${formatPrice(p.tax)}")
                    Text("Total: ${formatPrice(p.total)}", style = MaterialTheme.typography.titleMedium)
                }
            }
        }
        Button(
            onClick = {
                selectedAddressId?.let { addrId ->
                    checkout.placeOrder(
                        com.africanmarket.app.data.model.CheckoutRequest(
                            billingAddressId = addrId,
                            shippingAddressId = addrId,
                            paymentMethod = paymentMethod,
                            phoneNumber = if (paymentMethod == "mobile_money") phone else null,
                            mobileMoneyProvider = if (paymentMethod == "mobile_money") provider else null,
                        ),
                    )
                }
            },
            modifier = Modifier.fillMaxWidth().padding(16.dp),
            enabled = selectedAddressId != null && paymentMethod != "swish" && !state.loading,
        ) {
            if (state.loading) {
                CircularProgressIndicator(modifier = Modifier.size(20.dp))
            } else {
                Text("Place order")
            }
        }
    }
}

@Composable
fun AddressListScreen(navController: NavHostController) {
    val vm: AddressViewModel = viewModel()
    val addresses by vm.addresses.collectAsState()
    val state by vm.state.collectAsState()

    LaunchedEffect(Unit) { vm.load() }

    Column(Modifier.fillMaxSize()) {
        ScreenHeader("Addresses", onBack = { navController.popBackStack() })
        ErrorText(state.error)
        state.success?.let { Text(it, Modifier.padding(16.dp)) }
        LazyColumn(Modifier.weight(1f).padding(horizontal = 8.dp)) {
            items(addresses) { addr ->
                Card(Modifier.fillMaxWidth().padding(8.dp)) {
                    Row(Modifier.padding(12.dp), verticalAlignment = Alignment.CenterVertically) {
                        Column(Modifier.weight(1f)) {
                            Text(addr.fullName ?: "${addr.firstName ?: ""} ${addr.lastName ?: ""}".trim())
                            Text(addr.fullAddress ?: addr.addressLine1 ?: "", style = MaterialTheme.typography.bodySmall)
                            if (addr.isDefault == true) {
                                Text("Default", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.primary)
                            }
                        }
                        IconButton(onClick = { navController.navigate(Screen.AddressForm.create(addr.id)) }) {
                            Icon(Icons.Filled.Edit, contentDescription = "Edit")
                        }
                        IconButton(onClick = { addr.id?.let { vm.delete(it) } }) {
                            Icon(Icons.Filled.Delete, contentDescription = "Delete")
                        }
                    }
                }
            }
        }
        Button(
            onClick = { navController.navigate(Screen.AddressForm.create(null)) },
            modifier = Modifier.fillMaxWidth().padding(16.dp),
        ) { Text("Add address") }
    }
}

@Composable
fun AddressFormScreen(navController: NavHostController, editId: Int?) {
    val vm: AddressViewModel = viewModel()
    val state by vm.state.collectAsState()
    val addresses by vm.addresses.collectAsState()

    val existing = editId?.let { id -> addresses.find { it.id == id } }

    var firstName by remember { mutableStateOf(existing?.firstName ?: "") }
    var lastName by remember { mutableStateOf(existing?.lastName ?: "") }
    var addressLine1 by remember { mutableStateOf(existing?.addressLine1 ?: "") }
    var city by remember { mutableStateOf(existing?.city ?: "") }
    var country by remember { mutableStateOf(existing?.country ?: "NG") }
    var phone by remember { mutableStateOf(existing?.phone ?: "") }
    var stateField by remember { mutableStateOf(existing?.state ?: "") }
    var postal by remember { mutableStateOf(existing?.postalCode ?: "") }

    LaunchedEffect(Unit) { if (editId != null) vm.load() }

    LaunchedEffect(state.success) {
        if (state.success != null) {
            vm.clearMessage()
            navController.popBackStack()
        }
    }

    Column(Modifier.fillMaxSize().verticalScroll(rememberScrollState())) {
        ScreenHeader(if (editId != null) "Edit address" else "New address", onBack = { navController.popBackStack() })
        ErrorText(state.error)
        Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
            OutlinedTextField(value = firstName, onValueChange = { firstName = it }, label = { Text("First name") }, singleLine = true, modifier = Modifier.fillMaxWidth())
            OutlinedTextField(value = lastName, onValueChange = { lastName = it }, label = { Text("Last name") }, singleLine = true, modifier = Modifier.fillMaxWidth())
            OutlinedTextField(value = addressLine1, onValueChange = { addressLine1 = it }, label = { Text("Address line 1") }, singleLine = true, modifier = Modifier.fillMaxWidth())
            OutlinedTextField(value = city, onValueChange = { city = it }, label = { Text("City") }, singleLine = true, modifier = Modifier.fillMaxWidth())
            OutlinedTextField(value = country, onValueChange = { country = it }, label = { Text("Country") }, singleLine = true, modifier = Modifier.fillMaxWidth())
            OutlinedTextField(value = phone, onValueChange = { phone = it }, label = { Text("Phone") }, singleLine = true, modifier = Modifier.fillMaxWidth())
            OutlinedTextField(value = stateField, onValueChange = { stateField = it }, label = { Text("State (optional)") }, singleLine = true, modifier = Modifier.fillMaxWidth())
            OutlinedTextField(value = postal, onValueChange = { postal = it }, label = { Text("Postal code (optional)") }, singleLine = true, modifier = Modifier.fillMaxWidth())
            Button(
                onClick = {
                    vm.save(
                        AddressRequest(
                            firstName = firstName,
                            lastName = lastName,
                            addressLine1 = addressLine1,
                            city = city,
                            country = country,
                            phone = phone.takeIf { it.isNotBlank() },
                            state = stateField.takeIf { it.isNotBlank() },
                            postalCode = postal.takeIf { it.isNotBlank() },
                        ),
                        editId,
                    )
                },
                enabled = !state.loading && firstName.isNotBlank() && lastName.isNotBlank() && addressLine1.isNotBlank() && city.isNotBlank() && country.isNotBlank(),
                modifier = Modifier.fillMaxWidth(),
            ) {
                if (state.loading) CircularProgressIndicator(modifier = Modifier.size(20.dp)) else Text("Save")
            }
        }
    }
}
