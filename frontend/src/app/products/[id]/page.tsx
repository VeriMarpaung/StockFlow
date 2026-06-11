'use client';

import AppLayout from '@/components/AppLayout';
import api from '@/lib/api';
import { useParams, useRouter } from 'next/navigation';
import { useEffect, useState } from 'react';

type Product = {
  id: number; name: string; sku: string; price: number;
  stock: number; threshold: number; version: number;
  category?: { name: string };
};
type Transaction = {
  id: number; type: string; quantity: number;
  stock_before: number; stock_after: number;
  note: string | null; created_at: string;
  user?: { name: string };
};

export default function ProductDetailPage() {
  const params  = useParams();
  const router  = useRouter();
  const id      = params.id as string;
  const [product, setProduct]         = useState<Product | null>(null);
  const [transactions, setTransactions] = useState<Transaction[]>([]);

  useEffect(() => {
    if (!id) return;
    api.get(`/products/${id}`).then((r) => setProduct(r.data));
    api.get(`/products/${id}/transactions`).then((r) => setTransactions(r.data.data));
  }, [id]);

  if (!product) return <AppLayout><p className="text-gray-400">Loading…</p></AppLayout>;

  const isLow = product.stock <= product.threshold;

  return (
    <AppLayout>
      <button onClick={() => router.back()} className="text-sm text-blue-600 hover:underline mb-4">← Back</button>
      <div className="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div className="flex items-start justify-between">
          <div>
            <h1 className="text-xl font-bold text-gray-800">{product.name}</h1>
            <p className="text-gray-400 text-sm">{product.sku} · {product.category?.name}</p>
          </div>
          {isLow && (
            <span className="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">Low Stock</span>
          )}
        </div>
        <div className="grid grid-cols-3 gap-4 mt-4 text-sm">
          <div><p className="text-gray-500">Price</p><p className="font-semibold">Rp {Number(product.price).toLocaleString()}</p></div>
          <div><p className="text-gray-500">Stock</p><p className={`font-semibold text-lg ${isLow ? 'text-red-600' : ''}`}>{product.stock}</p></div>
          <div><p className="text-gray-500">Threshold</p><p className="font-semibold">{product.threshold}</p></div>
        </div>
        <div className="flex gap-3 mt-5">
          <button onClick={() => router.push(`/stock-in?product=${product.id}`)} className="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">
            Stock In
          </button>
          <button onClick={() => router.push(`/stock-out?product=${product.id}`)} className="bg-orange-500 text-white px-4 py-2 rounded text-sm hover:bg-orange-600">
            Stock Out
          </button>
        </div>
      </div>

      <h2 className="text-sm font-semibold text-gray-600 mb-3">Transaction History</h2>
      <div className="bg-white rounded-lg shadow-sm overflow-hidden">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 text-gray-600">
            <tr>
              {['Type', 'Qty', 'Before', 'After', 'Note', 'By', 'Date'].map((h) => (
                <th key={h} className="px-4 py-3 text-left font-medium">{h}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {transactions.map((t) => (
              <tr key={t.id} className="border-t border-gray-100">
                <td className="px-4 py-2">
                  <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${
                    t.type === 'in' ? 'bg-green-100 text-green-700' :
                    t.type === 'out' ? 'bg-red-100 text-red-700' :
                    'bg-yellow-100 text-yellow-700'
                  }`}>{t.type}</span>
                </td>
                <td className="px-4 py-2">{t.quantity}</td>
                <td className="px-4 py-2">{t.stock_before}</td>
                <td className="px-4 py-2">{t.stock_after}</td>
                <td className="px-4 py-2 text-gray-500">{t.note ?? '—'}</td>
                <td className="px-4 py-2 text-gray-500">{t.user?.name ?? '—'}</td>
                <td className="px-4 py-2 text-gray-400 text-xs">{new Date(t.created_at).toLocaleString()}</td>
              </tr>
            ))}
          </tbody>
        </table>
        {transactions.length === 0 && (
          <p className="text-center text-gray-400 py-6 text-sm">No transactions yet</p>
        )}
      </div>
    </AppLayout>
  );
}
