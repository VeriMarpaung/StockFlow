'use client';

import AppLayout from '@/components/AppLayout';
import api from '@/lib/api';
import Link from 'next/link';
import { useEffect, useState } from 'react';

type Product = {
  id: number;
  name: string;
  sku: string;
  price: number;
  stock: number;
  threshold: number;
  version: number;
  category?: { id: number; name: string };
};

export default function ProductsPage() {
  const [products, setProducts] = useState<Product[]>([]);
  const [search, setSearch]     = useState('');

  useEffect(() => {
    api.get('/products').then((r) => setProducts(r.data.data));
  }, []);

  const filtered = products.filter(
    (p) =>
      p.name.toLowerCase().includes(search.toLowerCase()) ||
      p.sku.toLowerCase().includes(search.toLowerCase())
  );

  return (
    <AppLayout>
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-xl font-bold text-gray-800">Products</h1>
        <Link
          href="/products/new"
          className="bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800"
        >
          + New Product
        </Link>
      </div>

      <input
        type="text"
        placeholder="Search by name or SKU…"
        value={search}
        onChange={(e) => setSearch(e.target.value)}
        className="w-full max-w-sm border border-gray-300 rounded px-3 py-2 text-sm mb-4"
      />

      <div className="bg-white rounded-lg shadow-sm overflow-hidden">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 text-gray-600">
            <tr>
              {['Name', 'SKU', 'Category', 'Price', 'Stock', 'Status', ''].map((h) => (
                <th key={h} className="px-4 py-3 text-left font-medium">
                  {h}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {filtered.map((p) => (
              <tr key={p.id} className="border-t border-gray-100 hover:bg-gray-50">
                <td className="px-4 py-3 font-medium">{p.name}</td>
                <td className="px-4 py-3 text-gray-500">{p.sku}</td>
                <td className="px-4 py-3 text-gray-500">{p.category?.name ?? '—'}</td>
                <td className="px-4 py-3">Rp {Number(p.price).toLocaleString()}</td>
                <td className="px-4 py-3">{p.stock}</td>
                <td className="px-4 py-3">
                  {p.stock <= p.threshold ? (
                    <span className="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs font-medium">
                      Low Stock
                    </span>
                  ) : (
                    <span className="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs font-medium">
                      OK
                    </span>
                  )}
                </td>
                <td className="px-4 py-3">
                  <Link href={`/products/${p.id}`} className="text-blue-600 hover:underline text-xs">
                    Detail
                  </Link>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        {filtered.length === 0 && (
          <p className="text-center text-gray-400 py-6 text-sm">No products found</p>
        )}
      </div>
    </AppLayout>
  );
}
