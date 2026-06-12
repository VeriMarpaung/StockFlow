import { Suspense } from 'react';
import StockInClient from './StockInClient';

export default function StockInPage() {
  return (
    <Suspense fallback={null}>
      <StockInClient />
    </Suspense>
  );
}
