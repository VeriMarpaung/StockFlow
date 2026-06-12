'use client';

import AppLayout from '@/components/AppLayout';
import api, { isAxiosError } from '@/lib/api';
import { useRouter, useSearchParams } from 'next/navigation';
import { useEffect, useState } from 'react';

type Product = { id: number; name: string; sku: string; stock: number };

export default function StockInClient() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const [products, setProducts] = useState<Product[]>([]);
  const [productId, setProductId] = useState(searchParams.get('product') ?? '');
  const [quantity, setQuantity] = useState('');
  const [note, setNote] = useState('');
  const [success, setSuccess] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    api.get('/products').then((r) => setProducts(r.data.data));
  }, []);

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    try {
      const res = await api.post(`/products/${productId}/stock-in`, {
        quantity: Number(quantity),
        note: note || undefined,
      });
      setSuccess(`Stok berhasil ditambah. Stok baru: ${res.data.stock_after}`);
      setQuantity('');
      setNote('');
    } catch (err) {
      if (isAxiosError(err) && err.response?.status === 422) {
        setError('Quantity harus minimal 1.');
      } else {
        setError('Terjadi kesalahan.');
      }
    } finally {
      setLoading(false);
    }
  };

  const selectedProduct = products.find((p) => String(p.id) === productId);

  return (
    <AppLayout>
      <h1 className="text-xl font-bold text-gray-800 mb-6">Stock In</h1>
      <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-md">
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Product</label>
            <select
              value={productId}
              onChange={(e) => setProductId(e.target.value)}
              required
              className="w-full border border-gray-300 rounded px-3 py-2 text-sm text-gray-800"
            >
              <option value="">Select product</option>
              {products.map((p) => (
                <option key={p.id} value={p.id}>
                  {p.name} (SKU: {p.sku}) - Stock: {p.stock}
                </option>
              ))}
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
            <input
              type="number"
              value={quantity}
              onChange={(e) => setQuantity(e.target.value)}
              min="1"
              required
              className="w-full border border-gray-300 rounded px-3 py-2 text-sm text-gray-800"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Note (optional)</label>
            <input
              value={note}
              onChange={(e) => setNote(e.target.value)}
              className="w-full border border-gray-300 rounded px-3 py-2 text-sm text-gray-800"
            />
          </div>
          {success && (
            <div className="flex items-center gap-2 text-green-700 bg-green-50 border border-green-200 px-3 py-2.5 rounded-lg text-sm">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor" className="w-4 h-4 shrink-0">
                <path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{success}</span>
            </div>
          )}
          {error && (
            <div className="flex items-center gap-2 text-red-600 bg-red-50 border border-red-200 px-3 py-2.5 rounded-lg text-sm">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-4 h-4 shrink-0">
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
              </svg>
              <span>{error}</span>
            </div>
          )}
          <div className="flex gap-3">
            <button
              type="submit"
              disabled={loading || !productId}
              className="bg-green-600 text-white px-5 py-2 rounded text-sm font-medium hover:bg-green-700 disabled:opacity-50"
            >
              {loading ? 'Processing...' : 'Add Stock'}
            </button>
            {selectedProduct && (
              <button
                type="button"
                onClick={() => router.push(`/products/${selectedProduct.id}`)}
                className="px-4 py-2 rounded text-sm border border-gray-300 hover:bg-gray-50"
              >
                View Product
              </button>
            )}
          </div>
        </form>
      </div>
    </AppLayout>
  );
}
