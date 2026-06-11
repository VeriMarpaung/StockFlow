'use client';

import AppLayout from '@/components/AppLayout';
import api, { isAxiosError } from '@/lib/api';
import { useRouter, useSearchParams } from 'next/navigation';
import { useEffect, useState } from 'react';

type Product = { id: number; name: string; sku: string; stock: number };

export default function StockInPage() {
  const router       = useRouter();
  const searchParams = useSearchParams();
  const [products, setProducts]     = useState<Product[]>([]);
  const [productId, setProductId]   = useState(searchParams.get('product') ?? '');
  const [quantity, setQuantity]     = useState('');
  const [note, setNote]             = useState('');
  const [success, setSuccess]       = useState('');
  const [error, setError]           = useState('');
  const [loading, setLoading]       = useState(false);

  useEffect(() => {
    api.get('/products').then((r) => setProducts(r.data.data));
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(''); setSuccess('');
    setLoading(true);
    try {
      const res = await api.post(`/products/${productId}/stock-in`, {
        quantity: Number(quantity),
        note: note || undefined,
      });
      setSuccess(`Stok berhasil ditambah. Stok baru: ${res.data.stock_after}`);
      setQuantity(''); setNote('');
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
      <div className="bg-white rounded-lg shadow-sm p-6 max-w-md">
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Product</label>
            <select
              value={productId}
              onChange={(e) => setProductId(e.target.value)}
              required
              className="w-full border border-gray-300 rounded px-3 py-2 text-sm"
            >
              <option value="">— Select product —</option>
              {products.map((p) => (
                <option key={p.id} value={p.id}>
                  {p.name} (SKU: {p.sku}) — Stock: {p.stock}
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
              className="w-full border border-gray-300 rounded px-3 py-2 text-sm"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Note (optional)</label>
            <input
              value={note}
              onChange={(e) => setNote(e.target.value)}
              className="w-full border border-gray-300 rounded px-3 py-2 text-sm"
            />
          </div>
          {success && <p className="text-green-700 bg-green-50 px-3 py-2 rounded text-sm">{success}</p>}
          {error && <p className="text-red-600 text-sm">{error}</p>}
          <div className="flex gap-3">
            <button type="submit" disabled={loading || !productId} className="bg-green-600 text-white px-5 py-2 rounded text-sm font-medium hover:bg-green-700 disabled:opacity-50">
              {loading ? 'Processing…' : 'Add Stock'}
            </button>
            {selectedProduct && (
              <button type="button" onClick={() => router.push(`/products/${selectedProduct.id}`)} className="px-4 py-2 rounded text-sm border border-gray-300 hover:bg-gray-50">
                View Product
              </button>
            )}
          </div>
        </form>
      </div>
    </AppLayout>
  );
}
