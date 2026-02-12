import React, { useEffect, useRef, useState } from "react";
import { MoreVertical, Search } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";

export default function ProductInfiniteList() {
  // Dummy data awal
  const [items, setItems] = useState(Array.from({ length: 20 }, (_, i) => i + 1));
  const loaderRef = useRef(null);

  // Logika sederhana Intersection Observer untuk Infinite Scroll
  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting) {
          // Simulasi memuat data baru
          setTimeout(() => {
            setItems((prev) => [...prev, ...Array.from({ length: 10 }, (_, i) => prev.length + i + 1)]);
          }, 500);
        }
      },
      { threshold: 1.0 }
    );

    if (loaderRef.current) observer.observe(loaderRef.current);
    return () => observer.disconnect();
  }, []);

  return (
    <div className="flex flex-col h-screen max-w-md mx-auto">
      {/* HEADER TETAP (STICKY) */}
      <div className="sticky top-0 z-10 py-2">
        <Input placeholder="Cari..." />
      </div>

      {/* DAFTAR ITEM (SCROLLABLE AREA) */}
      <div className="flex-1 overflow-y-auto">
        <div>
          {items.map((item) => (
            <div key={item} className="py-2 border-b last:border-0">
              <div className="flex items-center gap-3 text-xs">
                {/* Info Utama: Nama & SKU */}
                <div className="flex-1 min-w-0">
                  <p className="text-[10px] text-muted-foreground tabular-nums tracking-tight">
                    900602202535-{item}
                  </p>
                  <p className="font-medium text-sm truncate uppercase">Pita Serut Kecil</p>
                  <p className="text-[10px] tabular-nums font-medium text-slate-500">Stok: 544</p>
                </div>

                {/* Angka Stok & Harga */}
                <div className="text-right flex flex-col items-end gap-0.5 min-w-15">
                  <p className="font-bold tabular-nums text-primary">Rp. 1K</p>
                  <p className="text-[10px] text-muted-foreground">Asli: 0.45K</p>
                </div>

                {/* Kategori */}
                <Badge variant="secondary" className="text-[9px] px-1.5 py-0 uppercase font-bold tracking-tight h-fit">
                  GIFT
                </Badge>

                {/* Menu Aksi */}
                <div>
                  <Button size="icon" variant="ghost" className="h-8 w-8 text-muted-foreground">
                    <MoreVertical className="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </div>
          ))}

          {/* TRIGGER INFINITE SCROLL */}
          <div ref={loaderRef} className="py-8 flex justify-center">
            <div className="h-5 w-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin" />
          </div>
        </div>
      </div>
    </div>
  );
}