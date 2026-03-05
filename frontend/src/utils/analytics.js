/**
 * Analytics utility for pushing events to the GA4 dataLayer.
 * Follows official GA4 eCommerce event structure.
 */

export const trackEvent = (eventName, data = {}) => {
  if (typeof window !== 'undefined' && window.dataLayer) {
    window.dataLayer.push({
      event: eventName,
      ...data
    });
  }
};

export const trackEcommerceEvent = (eventName, ecommerceData) => {
  if (typeof window !== 'undefined' && window.dataLayer) {
    // Clear the previous ecommerce object to prevent overlapping data
    window.dataLayer.push({ ecommerce: null });
    
    window.dataLayer.push({
      event: eventName,
      ecommerce: ecommerceData
    });
  }
};

/**
 * Helper to ensure price is a number
 */
const parsePrice = (price) => {
  if (typeof price === 'number') return price;
  if (!price) return 0;
  // Remove currency symbols, commas, and other non-numeric chars except dot
  const sanitized = String(price).replace(/[^\d.]/g, '');
  return parseFloat(sanitized) || 0;
};

/**
 * Standard eCommerce Events
 */

export const trackViewItem = (product) => {
  const price = parsePrice(product.sellable_price || product.price);
  trackEcommerceEvent('view_item', {
    currency: 'BDT',
    value: price,
    items: [{
      item_id: String(product.id || product.sku || ''),
      item_name: product.name,
      price: price,
      item_brand: product.brand || '',
      item_category: product.category || '',
      quantity: 1
    }]
  });
};

export const trackAddToCart = (product, quantity = 1) => {
  const price = parsePrice(product.price);
  trackEcommerceEvent('add_to_cart', {
    currency: 'BDT',
    value: price * quantity,
    items: [{
      item_id: String(product.id || product.product_id || product.sku || ''),
      item_name: product.name || product.product_name,
      price: price,
      item_brand: product.brand || '',
      item_category: product.category || '',
      quantity: quantity
    }]
  });
};

export const trackBeginCheckout = (cartItems, totalValue) => {
  trackEcommerceEvent('begin_checkout', {
    currency: 'BDT',
    value: parsePrice(totalValue),
    items: cartItems.map(item => ({
      item_id: String(item.id || item.product_id || item.sku || ''),
      item_name: item.name || item.product_name,
      price: parsePrice(item.price),
      quantity: item.quantity
    }))
  });
};

export const trackPurchase = (order) => {
  const total = parsePrice(order.total);
  trackEcommerceEvent('purchase', {
    transaction_id: order.order_number || order.id,
    value: total,
    currency: order.currency || 'BDT',
    items: order.items.map(item => ({
      item_id: String(item.product_id || item.id || item.sku || '-'),
      item_name: item.product_name || item.name,
      price: parsePrice(item.price),
      quantity: item.quantity
    }))
  });
};

/**
 * User Interaction Events
 */

export const trackSignUp = (method = 'email') => {
  trackEvent('sign_up', { method });
};

export const trackSignIn = (method = 'email') => {
  trackEvent('sign_in', { method });
};

export const trackTravelerSignUp = () => {
  trackEvent('Traveler_Signup');
};
