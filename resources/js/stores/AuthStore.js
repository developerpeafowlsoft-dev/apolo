import axios from "axios";
import { defineStore } from "pinia";
import { useBasketStore } from "./BasketStore";
import { useChat } from "./ChatStore";

export const useAuth = defineStore("authStore", {
    state: () => ({
        user: null,
        addresses: [],
        token: null,
        favoriteProducts: 0,
        loginModal: false,
        registerModal: false,
        showAddressModal: false,
        showChangeAddressModal: false,
        orderCancel: false,
        favoriteRemove: false,
    }),

    getters: {
        getAddressById: (state) => (id) => {
            return state.addresses.find((address) => address.id == id);
        },
    },

    actions: {
        setToken(token) {
            this.token = `Bearer ${token}`;
        },
        setUser(user) {
            this.user = user;
        },

        showLoginModal() {
            this.loginModal = true;
        },

        hideLoginModal() {
            this.loginModal = false;
        },

        fetchAddresses() {
            return axios
                .get("/addresses", {
                    headers: {
                        Authorization: this.token,
                    },
                })
                .then((response) => {
                    this.addresses = response.data.data.addresses || [];
                    const basketStore = useBasketStore();
                    if (this.addresses.length > 0) {
                        const defaultAddress = this.addresses.find((address) => address.is_default);
                        if (defaultAddress) {
                            basketStore.address = defaultAddress;
                        } else if (!basketStore.address || !this.addresses.some(a => a.id === basketStore.address.id)) {
                            basketStore.address = this.addresses[0];
                        }
                    } else {
                        basketStore.address = {};
                    }
                    return response;
                })
                .catch((error) => {
                    if (error.response && error.response.status === 401) {
                        this.token = null;
                        this.user = null;
                        this.addresses = [];
                        this.favoriteProducts = 0;
                    }
                });
        },
        deleteAddress(addressId) {
            return axios
                .delete(`/address/${addressId}/delete`, {
                    headers: {
                        Authorization: this.token,
                    },
                })
                .then((response) => {
                    const basketStore = useBasketStore();
                    if (basketStore.address && basketStore.address.id == addressId) {
                        basketStore.address = {};
                    }
                    return this.fetchAddresses();
                });
        },
        fetchFavoriteProducts() {
            if (this.token) {
                axios
                    .get("/favorite-products", {
                        headers: {
                            Authorization: this.token,
                        },
                    })
                    .then((response) => {
                        this.favoriteProducts =
                            response.data.data.products?.length ?? 0;
                    })
                    .catch((error) => {
                        if (error.response.status === 401) {
                            this.token = null;
                            this.user = null;
                            this.addresses = [];
                        }
                    });
            } else {
                this.favoriteProducts = 0;
            }
        },

        logout() {
            axios
                .get("/logout", {
                    headers: {
                        Authorization: this.token,
                    },
                })
                .then((response) => {
                    const chatStore = useChat();
                    this.user = null;
                    this.addresses = [];
                    this.token = null;
                    this.favoriteProducts = 0;
                    chatStore.chats = [];
                    chatStore.activeShop = null;
                })
                .catch((error) => {
                    this.user = null;
                    this.addresses = [];
                    this.token = null;
                    this.favoriteProducts = 0;
                });
        },
    },

    persist: true,
});
