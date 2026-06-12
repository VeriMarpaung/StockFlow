import { Suspense } from 'react';
import StockOutClient from './StockOutClient';

export default function StockOutPage() {
  return (
    <Suspense fallback={null}>
      <StockOutClient />
    </Suspense>
  );
}
