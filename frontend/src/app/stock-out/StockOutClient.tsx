'use client';

import AppLayout from '@/components/AppLayout';
import api, { isAxiosError } from '@/lib/api';
import { useRouter, useSearchParams } from 'next/navigation';
import { useEffect, useState } from 'react';

type Product = { id: number; name: string; sku: string; stock: number; threshold: number; version: number };

export default function StockOutClient() {
  const router       = useRouter();
  const searchParams = useSearchParams();
  const [products, setProducts]   = useState<Product[]>([]);
  const [productId, setProductId] = useState(searchParams.get('product') ?? '');
  const [quantity, setQuantity]   = useState('');
  const [note, setNote]           = useState('');
  const [success, setSuccess]     = useState('');
  const [error, setError]         = useState('');
  const [conflictMsg, setConflict] = useState('');
  const [loading, setLoading]     = useState(false);

  const loadProducts = () =>
    api.get('/products').then((r) => {
      const list = r.data.data as Product[];
      setProducts(list);
      return list;
    });

  useEffect(() => { loadProducts(); }, []);

  const selectedProduct = products.find((p) => String(p.id) === productId);

  const handleProductChange = (id: string) => {
    setProductId(id);
    setConflict('');
    setError('');
    setSuccess('');
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(''); setSuccess(''); setConflict('');
    if (!selectedProduct) return;
    setLoading(true);
    try {
      const res = await api.post(`/products/${selectedProduct.id}/stock-out`, {
        quantity: Number(quantity),
        version: selectedProduct.version,
        note: note || undefined,
      });
      setSuccess(`Stok berhasil dikurangi. Stok baru: ${res.data.stock_after}`);
      setQuantity(''); setNote('');
      await loadProducts();
    } catch (err) {
      if (isAxiosError(err)) {
        if (err.response?.status === 409) {
          const fresh = await loadProducts();
          const refreshed = fresh.find((p) => String(p.id) === productId);
          setConflict(
            `Data telah berubah oleh user lain. Data sudah direfresh (version: ${refreshed?.version ?? '?'}). Silakan coba lagi.`
          );
        } else if (err.response?.status === 422) {
          const msg = err.response.data?.message ?? 'Stok tidak mencukupi.';
          setError(msg);
        } else {
          setError('Terjadi kesalahan.');
        }
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <AppLayout>
      <h1 className="text-xl font-bold text-gray-800 mb-6">Stock Out</h1>
      <div className="bg-white rounded-lg shadow-sm p-6 max-w-md">
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Product</label>
            <select
              value={productId}
              onChange={(e) => handleProductChange(e.target.value)}
              required
              className="w-full border border-gray-300 rounded px-3 py-2 text-sm"
            >
              <option value="">— Select product —</option>
              {products.map((p) => (
                <option key={p.id} value={p.id}>
                  {p.name} — Stock: {p.stock} (v{p.version})
                </option>
              ))}
            </select>
            {selectedProduct && (
              <p className="text-xs text-gray-400 mt-1">
                Current stock: {selectedProduct.stock} · version: {selectedProduct.version}
              </p>
            )}
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
            <input
              type="number"
              value={quantity}
              onChange={(e) => setQuantity(e.target.value)}
              min="1"
              max={selectedProduct?.stock}
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

          {conflictMsg && (
            <div className="bg-yellow-50 border border-yellow-300 text-yellow-800 rounded p-3 text-sm">
              <p className="font-semibold">Conflict Terdeteksi</p>
              <p className="mt-1">{conflictMsg}</p>
            </div>
          )}

          {success && (
            <p className="text-green-700 bg-green-50 px-3 py-2 rounded text-sm">{success}</p>
          )}
          {error && <p className="text-red-600 text-sm">{error}</p>}

          <div className="flex gap-3">
            <button
              type="submit"
              disabled={loading || !productId}
              className="bg-orange-500 text-white px-5 py-2 rounded text-sm font-medium hover:bg-orange-600 disabled:opacity-50"
            >
              {loading ? 'Processing...' : 'Stock Out'}
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

      <div className="mt-6 bg-blue-50 border border-blue-200 rounded p-4 max-w-md text-sm text-blue-800">
        <p className="font-semibold">Demo Race Condition:</p>
        <p className="mt-1">Buka halaman ini di 2 tab browser. Pilih produk yang sama, lalu submit stock-out hampir bersamaan. Tab kedua akan mendapat pesan Conflict Terdeteksi.</p>
      </div>
    </AppLayout>
  );
}
