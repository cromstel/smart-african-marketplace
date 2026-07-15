package com.africanmarket.app.ui.screens

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
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavHostController
import com.africanmarket.app.data.model.Order
import com.africanmarket.app.ui.components.CenteredProgress
import com.africanmarket.app.ui.components.ErrorText
import com.africanmarket.app.ui.components.ScreenHeader
import com.africanmarket.app.ui.navigation.Screen
import com.africanmarket.app.ui.viewmodel.AuthViewModel
import com.africanmarket.app.ui.viewmodel.OrderViewModel
import com.africanmarket.app.util.formatPrice
import androidx.compose.runtime.collectAsState

@Composable
fun OrdersScreen(navController: NavHostController) {
    val vm: OrderViewModel = viewModel()
    val orders by vm.orders.collectAsState()
    val state by vm.state.collectAsState()

    LaunchedEffect(Unit) { vm.load() }

    Column(Modifier.fillMaxSize()) {
        ScreenHeader("Orders")
        ErrorText(state.error)
        when {
            state.loading && orders.isEmpty() -> CenteredProgress()
            orders.isEmpty() -> Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                Text("No orders yet")
            }
            else -> LazyColumn(Modifier.fillMaxSize().padding(horizontal = 8.dp)) {
                items(orders) { order -> OrderRow(order) { navController.navigate(Screen.OrderDetail.create(order.id)) } }
            }
        }
    }
}

@Composable
fun OrderRow(order: Order, onClick: () -> Unit) {
    Card(
        Modifier.fillMaxWidth().padding(8.dp).clickable(onClick = onClick),
    ) {
        Column(Modifier.padding(12.dp)) {
            Text(order.orderNumber ?: "#${order.id}", style = MaterialTheme.typography.titleMedium)
            Text("Total: ${formatPrice(order.total)}  •  ${order.status ?: ""}")
            Text(
                "Payment: ${order.paymentStatus ?: order.paymentMethod ?: ""}",
                style = MaterialTheme.typography.bodySmall,
            )
        }
    }
}

@Composable
fun OrderDetailScreen(navController: NavHostController, id: Int) {
    val vm: OrderViewModel = viewModel()
    val order by vm.detail.collectAsState()
    val state by vm.state.collectAsState()

    LaunchedEffect(Unit) { vm.loadDetail(id) }

    Column(Modifier.fillMaxSize()) {
        ScreenHeader("Order", onBack = { navController.popBackStack() })
        ErrorText(state.error)
        val o = order
        if (o == null) {
            if (state.loading) CenteredProgress() else ErrorText("Order not found")
            return
        }
        Column(Modifier.fillMaxSize().verticalScroll(rememberScrollState()).padding(16.dp)) {
            Text(o.orderNumber ?: "#${o.id}", style = MaterialTheme.typography.titleLarge)
            Text("Status: ${o.status ?: "-"}   Payment: ${o.paymentStatus ?: "-"}")
            Spacer(Modifier.height(8.dp))
            o.items?.forEach { item ->
                Card(Modifier.fillMaxWidth().padding(vertical = 4.dp)) {
                    Column(Modifier.padding(12.dp)) {
                        Text(item.productName ?: "Item")
                        Text("Qty: ${item.quantity}   ${formatPrice(item.total)}")
                    }
                }
            }
            Spacer(Modifier.height(8.dp))
            Text("Subtotal: ${formatPrice(o.subtotal)}")
            Text("Shipping: ${formatPrice(o.shippingCost)}")
            Text("Total: ${formatPrice(o.total)}", style = MaterialTheme.typography.titleMedium)
        }
    }
}

@Composable
fun ProfileScreen(navController: NavHostController) {
    val auth: AuthViewModel = viewModel()
    val user by auth.user.collectAsState()
    val action by auth.action.collectAsState()
    val isLoggedIn by auth.isLoggedIn.collectAsState()

    LaunchedEffect(Unit) { if (isLoggedIn) auth.loadUser() }

    Column(Modifier.fillMaxSize()) {
        ScreenHeader("Profile")
        Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
            if (!isLoggedIn) {
                Text("You are not signed in.")
                Button(
                    onClick = { navController.navigate(Screen.Login.route) },
                    modifier = Modifier.fillMaxWidth(),
                ) { Text("Sign in") }
            } else {
                Text(user?.name ?: user?.email ?: "", style = MaterialTheme.typography.titleMedium)
                Text(user?.email ?: "")
                Spacer(Modifier.height(8.dp))
                ErrorText(action.error)
                Button(
                    onClick = { navController.navigate(Screen.Orders.route) },
                    modifier = Modifier.fillMaxWidth(),
                ) { Text("My orders") }
                Button(
                    onClick = { navController.navigate(Screen.Addresses.route) },
                    modifier = Modifier.fillMaxWidth(),
                ) { Text("My addresses") }
                OutlinedButton(
                    onClick = {
                        auth.logout()
                        navController.navigate(Screen.Login.route) {
                            popUpTo(Screen.Home.route) { inclusive = true }
                        }
                    },
                    modifier = Modifier.fillMaxWidth(),
                ) { Text("Sign out") }
            }
        }
    }
}
