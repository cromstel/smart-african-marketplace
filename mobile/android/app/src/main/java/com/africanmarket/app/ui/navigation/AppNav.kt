package com.africanmarket.app.ui.navigation

import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.ReceiptLong
import androidx.compose.material.icons.filled.ShoppingCart
import androidx.compose.material3.Icon
import androidx.compose.material3.NavigationBar
import androidx.compose.material3.NavigationBarItem
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavHostController
import androidx.navigation.NavType
import androidx.navigation.navArgument
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.currentBackStackEntryAsState
import androidx.navigation.compose.rememberNavController
import com.africanmarket.app.ui.screens.*
import com.africanmarket.app.ui.viewmodel.AuthViewModel

sealed class Screen(val route: String) {
    object Splash : Screen("splash")
    object Login : Screen("login")
    object Register : Screen("register")
    object Home : Screen("home")
    object ProductDetail : Screen("productDetail/{id}") {
        fun create(id: Int) = "productDetail/$id"
    }
    object Cart : Screen("cart")
    object Checkout : Screen("checkout")
    object Addresses : Screen("addresses")
    object AddressForm : Screen("addressForm?editId={editId}") {
        fun create(editId: Int?) = "addressForm?editId=${editId ?: -1}"
    }
    object Orders : Screen("orders")
    object OrderDetail : Screen("orderDetail/{id}") {
        fun create(id: Int) = "orderDetail/$id"
    }
    object Profile : Screen("profile")
}

private val bottomItems = listOf(
    Triple(Screen.Home.route, "Home", Icons.Filled.Home),
    Triple(Screen.Cart.route, "Cart", Icons.Filled.ShoppingCart),
    Triple(Screen.Orders.route, "Orders", Icons.Filled.ReceiptLong),
    Triple(Screen.Profile.route, "Profile", Icons.Filled.Person),
)

@Composable
fun AppNav(startedViaPayment: Boolean) {
    val navController: NavHostController = rememberNavController()
    val auth: AuthViewModel = viewModel()
    val navBackStackEntry by navController.currentBackStackEntryAsState()
    val currentRoute = navBackStackEntry?.destination?.route
    val startDestination = if (startedViaPayment) Screen.Orders.route else Screen.Splash.route
    val showBottom = bottomItems.any { currentRoute == it.first }

    Scaffold(
        bottomBar = {
            if (showBottom) {
                NavigationBar {
                    bottomItems.forEach { (route, label, icon: ImageVector) ->
                        NavigationBarItem(
                            selected = currentRoute == route,
                            onClick = {
                                navController.navigate(route) {
                                    popUpTo(navController.graph.startDestinationId) { saveState = true }
                                    launchSingleTop = true
                                    restoreState = true
                                }
                            },
                            icon = { Icon(icon, contentDescription = label) },
                            label = { Text(label) },
                        )
                    }
                }
            }
        },
    ) { innerPadding ->
        NavHost(
            navController = navController,
            startDestination = startDestination,
            modifier = Modifier.padding(innerPadding),
        ) {
            composable(Screen.Splash.route) { SplashScreen(navController) }
            composable(Screen.Login.route) { LoginScreen(navController) }
            composable(Screen.Register.route) { RegisterScreen(navController) }
            composable(Screen.Home.route) { HomeScreen(navController) }
            composable(
                Screen.ProductDetail.route,
                arguments = listOf(navArgument("id") { type = NavType.IntType }),
            ) { backStack ->
                val id = backStack.arguments?.getInt("id") ?: 0
                ProductDetailScreen(navController, id)
            }
            composable(Screen.Cart.route) { CartScreen(navController) }
            composable(Screen.Checkout.route) { CheckoutScreen(navController) }
            composable(Screen.Addresses.route) { AddressListScreen(navController) }
            composable(
                Screen.AddressForm.route,
                arguments = listOf(navArgument("editId") { type = NavType.IntType }),
            ) { backStack ->
                val editId = backStack.arguments?.getInt("editId")?.takeIf { it != -1 }
                AddressFormScreen(navController, editId)
            }
            composable(Screen.Orders.route) { OrdersScreen(navController) }
            composable(
                Screen.OrderDetail.route,
                arguments = listOf(navArgument("id") { type = NavType.IntType }),
            ) { backStack ->
                val id = backStack.arguments?.getInt("id") ?: 0
                OrderDetailScreen(navController, id)
            }
            composable(Screen.Profile.route) { ProfileScreen(navController) }
        }
    }
}

