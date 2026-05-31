export interface User {
  id: number
  name: string
  email: string
  role: 'customer' | 'admin' | 'staff'
  avatar?: string
  phone?: string
  created_at: string
}

export interface Category {
  id: number
  name: string
  slug: string
  description?: string
  image?: string
  parent_id?: number
  sort_order: number
  is_active: boolean
  seo_title?: string
  seo_description?: string
}

export interface ProductImage {
  id: number
  product_id: number
  url: string
  alt?: string
  sort_order: number
}

export interface ContentBlock {
  id?: number
  type: 'rich_text' | 'image' | 'gallery' | 'video' | 'faq' | 'ingredients' | 'feature_cards' | 'promotional_banner'
  content: Record<string, unknown>
  sort_order: number
}

export interface Product {
  id: number
  category_id?: number
  name: string
  slug: string
  sku: string
  price: string | number
  sale_price?: string | number | null
  description?: string
  thumbnail?: string
  stock: number
  is_featured: boolean
  is_best_seller: boolean
  is_active: boolean
  seo_title?: string
  seo_description?: string
  seo_keywords?: string
  effective_price?: number
  average_rating?: number
  category?: Category
  images?: ProductImage[]
  content_blocks?: ContentBlock[]
  approved_reviews?: Review[]
  created_at: string
}

export interface Review {
  id: number
  user_id: number
  product_id: number
  rating: number
  title?: string
  body: string
  is_approved: boolean
  user?: User
  created_at: string
}

export interface CartItem {
  id: number
  product_id: number
  quantity: number
  product: Product
}

export interface Cart {
  items: CartItem[]
  subtotal: number
  count: number
}

export interface OrderItem {
  id: number
  product_id?: number
  product_name: string
  sku: string
  thumbnail?: string
  price: number
  quantity: number
  subtotal: number
}

export interface ShippingAddress {
  name: string
  phone: string
  address_line1: string
  address_line2?: string
  city: string
  province: string
  postal_code: string
  country: string
}

export interface Order {
  id: number
  order_number: string
  status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled' | 'refunded'
  subtotal: number
  discount: number
  shipping_fee: number
  tax: number
  total: number
  payment_status: 'pending' | 'paid' | 'failed' | 'refunded'
  payment_method: string
  items: OrderItem[]
  shipping_address?: ShippingAddress
  created_at: string
}

export interface CarouselSlide {
  id: number
  desktop_image: string
  mobile_image?: string
  title?: string
  subtitle?: string
  cta_text?: string
  cta_url?: string
  sort_order: number
  is_active: boolean
}

export interface HomepageSection {
  id: number
  type: 'hero_carousel' | 'featured_products' | 'best_sellers' | 'categories' | 'promotional_banner' | 'about' | 'reviews' | 'newsletter'
  name: string
  sort_order: number
  is_active: boolean
  settings?: Record<string, unknown> | null
  data?: unknown
}

export interface Banner {
  id: number
  title: string
  image: string
  mobile_image?: string
  link_url?: string
  description?: string
  is_active: boolean
}

export interface Coupon {
  id: number
  code: string
  type: 'fixed' | 'percentage'
  value: number
  min_order_amount: number
  max_discount?: number
  expires_at?: string
  is_active: boolean
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}
