package com.africanmarket.app.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.africanmarket.app.data.model.*
import com.africanmarket.app.data.repository.*
import com.africanmarket.app.data.store.TokenDataStore
import com.google.gson.Gson
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

data class ActionState(
    val loading: Boolean = false,
    val error: String? = null,
    val success: String? = null,
)

data class ListState(
    val loading: Boolean = false,
    val error: String? = null,
)

class AuthViewModel : ViewModel() {
    private val gson = Gson()

    private val _user = MutableStateFlow<User?>(null)
    val user: StateFlow<User?> = _user.asStateFlow()

    private val _isLoggedIn = MutableStateFlow(false)
    val isLoggedIn: StateFlow<Boolean> = _isLoggedIn.asStateFlow()

    private val _action = MutableStateFlow(ActionState())
    val action: StateFlow<ActionState> = _action.asStateFlow()

    fun refreshLoginState() {
        _isLoggedIn.value = !TokenDataStore.getCachedToken().isNullOrEmpty()
    }

    fun loadUser() {
        viewModelScope.launch {
            when (val r = AuthRepository.me()) {
                is ApiResult.Success -> {
                    _user.value = r.data
                    _isLoggedIn.value = true
                }
                is ApiResult.Error -> if (r.code == 401) logoutLocal()
            }
        }
    }

    fun login(email: String, password: String) {
        viewModelScope.launch {
            _action.value = ActionState(loading = true)
            when (val r = AuthRepository.login(email, password)) {
                is ApiResult.Success -> {
                    val token = r.data.token
                    val u = r.data.user
                    if (!token.isNullOrEmpty() && u != null) {
                        TokenDataStore.saveToken(token, gson.toJson(u))
                        _user.value = u
                        _isLoggedIn.value = true
                        _action.value = ActionState(success = r.data.message ?: "Welcome")
                    } else {
                        _action.value = ActionState(error = "Invalid server response")
                    }
                }
                is ApiResult.Error -> _action.value = ActionState(error = r.message)
            }
        }
    }

    fun register(name: String, email: String, password: String, passwordConfirmation: String) {
        viewModelScope.launch {
            _action.value = ActionState(loading = true)
            when (val r = AuthRepository.register(name, email, password, passwordConfirmation)) {
                is ApiResult.Success -> {
                    val token = r.data.token
                    val u = r.data.user
                    if (!token.isNullOrEmpty() && u != null) {
                        TokenDataStore.saveToken(token, gson.toJson(u))
                        _user.value = u
                        _isLoggedIn.value = true
                        _action.value = ActionState(success = r.data.message ?: "Account created")
                    } else {
                        _action.value = ActionState(error = "Invalid server response")
                    }
                }
                is ApiResult.Error -> _action.value = ActionState(error = r.message)
            }
        }
    }

    fun updateProfile(firstname: String?, lastname: String?, email: String?, phone: String?) {
        viewModelScope.launch {
            _action.value = ActionState(loading = true)
            when (val r = AuthRepository.updateProfile(UpdateProfileRequest(firstname, lastname, email, phone))) {
                is ApiResult.Success -> {
                    _user.value = r.data
                    _action.value = ActionState(success = "Profile updated")
                }
                is ApiResult.Error -> _action.value = ActionState(error = r.message)
            }
        }
    }

    fun logout() {
        viewModelScope.launch {
            AuthRepository.logout()
            logoutLocal()
        }
    }

    private suspend fun logoutLocal() {
        TokenDataStore.clear()
        _user.value = null
        _isLoggedIn.value = false
    }

    fun clearAction() {
        _action.value = ActionState()
    }
}

class ProductViewModel : ViewModel() {
    private val _products = MutableStateFlow<List<Product>>(emptyList())
    val products: StateFlow<List<Product>> = _products.asStateFlow()

    private val _state = MutableStateFlow(ListState())
    val state: StateFlow<ListState> = _state.asStateFlow()

    var search: String? = null
    var categoryId: Int? = null
    var sort: String? = null

    private var page = 1
    private var lastPage = 1
    private var loadingMore = false

    fun refresh() {
        page = 1
        load(initial = true)
    }

    fun loadMore() {
        if (page <= lastPage && !loadingMore) load(initial = false)
    }

    private fun load(initial: Boolean) {
        viewModelScope.launch {
            loadingMore = true
            _state.value = _state.value.copy(loading = true, error = null)
            when (val r = ProductRepository.products(page, search, categoryId, sort)) {
                is ApiResult.Success -> {
                    val data = r.data.data ?: emptyList()
                    lastPage = r.data.meta?.lastPage ?: 1
                    _products.value = if (initial) data else _products.value + data
                    if (data.isNotEmpty()) page++
                    _state.value = _state.value.copy(loading = false)
                }
                is ApiResult.Error -> _state.value = _state.value.copy(loading = false, error = r.message)
            }
            loadingMore = false
        }
    }
}

class CartViewModel : ViewModel() {
    private val _cart = MutableStateFlow<Cart?>(null)
    val cart: StateFlow<Cart?> = _cart.asStateFlow()

    private val _state = MutableStateFlow(ActionState())
    val state: StateFlow<ActionState> = _state.asStateFlow()

    fun load() {
        viewModelScope.launch {
            _state.value = ActionState(loading = true)
            when (val r = CartRepository.getCart()) {
                is ApiResult.Success -> {
                    _cart.value = r.data
                    _state.value = ActionState()
                }
                is ApiResult.Error -> _state.value = ActionState(error = r.message)
            }
        }
    }

    fun add(productId: Int, quantity: Int = 1, variantId: Int? = null) {
        viewModelScope.launch {
            _state.value = ActionState(loading = true)
            when (val r = CartRepository.addToCart(productId, quantity, variantId)) {
                is ApiResult.Success -> {
                    _cart.value = r.data
                    _state.value = ActionState(success = "Added to cart")
                }
                is ApiResult.Error -> _state.value = ActionState(error = r.message)
            }
        }
    }

    fun clearMessage() {
        _state.value = ActionState()
    }
}

class CheckoutViewModel : ViewModel() {
    private val _addresses = MutableStateFlow<List<Address>>(emptyList())
    val addresses: StateFlow<List<Address>> = _addresses.asStateFlow()

    private val _preview = MutableStateFlow<ShippingPreview?>(null)
    val preview: StateFlow<ShippingPreview?> = _preview.asStateFlow()

    private val _result = MutableStateFlow<CheckoutOutcome?>(null)
    val result: StateFlow<CheckoutOutcome?> = _result.asStateFlow()

    private val _state = MutableStateFlow(ActionState())
    val state: StateFlow<ActionState> = _state.asStateFlow()

    fun loadAddresses() {
        viewModelScope.launch {
            when (val r = AddressRepository.list()) {
                is ApiResult.Success -> _addresses.value = r.data
                is ApiResult.Error -> _state.value = ActionState(error = r.message)
            }
        }
    }

    fun preview(addressId: Int) {
        viewModelScope.launch {
            when (val r = CheckoutRepository.shippingPreview(addressId)) {
                is ApiResult.Success -> _preview.value = r.data
                is ApiResult.Error -> _state.value = ActionState(error = r.message)
            }
        }
    }

    fun placeOrder(req: CheckoutRequest) {
        viewModelScope.launch {
            _state.value = ActionState(loading = true)
            when (val r = CheckoutRepository.checkout(req)) {
                is ApiResult.Success -> _result.value = CheckoutOutcome.OrderPlaced(r.data)
                is ApiResult.Error -> _state.value = ActionState(error = r.message)
            }
        }
    }

    fun requestPayment(url: String?) {
        _result.value = CheckoutOutcome.OpenPayment(url)
    }

    fun clearResult() {
        _result.value = null
        _state.value = ActionState()
    }
}

sealed class CheckoutOutcome {
    data class OrderPlaced(val data: CheckoutResponse) : CheckoutOutcome()
    data class OpenPayment(val url: String?) : CheckoutOutcome()
}

class AddressViewModel : ViewModel() {
    private val _addresses = MutableStateFlow<List<Address>>(emptyList())
    val addresses: StateFlow<List<Address>> = _addresses.asStateFlow()

    private val _state = MutableStateFlow(ActionState())
    val state: StateFlow<ActionState> = _state.asStateFlow()

    fun load() {
        viewModelScope.launch {
            _state.value = ActionState(loading = true)
            when (val r = AddressRepository.list()) {
                is ApiResult.Success -> {
                    _addresses.value = r.data
                    _state.value = ActionState()
                }
                is ApiResult.Error -> _state.value = ActionState(error = r.message)
            }
        }
    }

    fun save(req: AddressRequest, id: Int?) {
        viewModelScope.launch {
            _state.value = ActionState(loading = true)
            val r = if (id == null) AddressRepository.create(req) else AddressRepository.update(id, req)
            when (r) {
                is ApiResult.Success -> {
                    load()
                    _state.value = ActionState(success = "Saved")
                }
                is ApiResult.Error -> _state.value = ActionState(error = r.message)
            }
        }
    }

    fun delete(id: Int) {
        viewModelScope.launch {
            AddressRepository.delete(id)
            load()
        }
    }

    fun clearMessage() {
        _state.value = ActionState()
    }
}

class OrderViewModel : ViewModel() {
    private val _orders = MutableStateFlow<List<Order>>(emptyList())
    val orders: StateFlow<List<Order>> = _orders.asStateFlow()

    private val _detail = MutableStateFlow<Order?>(null)
    val detail: StateFlow<Order?> = _detail.asStateFlow()

    private val _state = MutableStateFlow(ActionState())
    val state: StateFlow<ActionState> = _state.asStateFlow()

    fun load() {
        viewModelScope.launch {
            _state.value = ActionState(loading = true)
            when (val r = OrderRepository.orders(1)) {
                is ApiResult.Success -> {
                    _orders.value = r.data.data ?: emptyList()
                    _state.value = ActionState()
                }
                is ApiResult.Error -> _state.value = ActionState(error = r.message)
            }
        }
    }

    fun loadDetail(id: Int) {
        viewModelScope.launch {
            _state.value = ActionState(loading = true)
            when (val r = OrderRepository.order(id)) {
                is ApiResult.Success -> {
                    _detail.value = r.data
                    _state.value = ActionState()
                }
                is ApiResult.Error -> _state.value = ActionState(error = r.message)
            }
        }
    }

    fun clearDetail() {
        _detail.value = null
    }
}
