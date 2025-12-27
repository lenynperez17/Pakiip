

"use client";

import Image from "next/image";
import { useState, useEffect } from "react";
import { cn } from "@/lib/utils";

interface PakiipCharacterProps {
  message: string;
  imageUrl: string;
  onComplete?: () => void;
  duration?: number;
}

export function PakiipCharacter({ message, imageUrl, onComplete, duration = 4000 }: PakiipCharacterProps) {
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const showTimer = setTimeout(() => {
      setIsVisible(true);
    }, 100);

    const hideTimer = setTimeout(() => {
        setIsVisible(false);
        if (onComplete) {
            // Give it time to fade out before calling complete
            setTimeout(onComplete, 500);
        }
    }, duration);

    return () => {
      clearTimeout(showTimer);
      clearTimeout(hideTimer);
    };
  }, [duration, onComplete]);


  return (
    <div
      className={cn(
        "fixed inset-0 z-50 flex flex-col items-center justify-center bg-background/80 backdrop-blur-sm transition-opacity duration-500",
        isVisible ? "opacity-100" : "opacity-0 pointer-events-none"
      )}
    >
      <div className={cn(
        "flex flex-col items-center gap-4 transition-all duration-500 ease-out",
        isVisible ? "opacity-100 translate-y-0 scale-100" : "opacity-0 -translate-y-4 scale-95"
      )}>
        <div className="relative bg-card p-4 rounded-lg shadow-xl border max-w-sm text-center">
          <p className="text-lg font-semibold text-foreground">{message}</p>
          <div className="absolute top-full left-1/2 -translate-x-1/2 mt-[-1px] w-0 h-0 border-l-[15px] border-l-transparent border-r-[15px] border-r-transparent border-t-[20px] border-t-card"></div>
        </div>
        <img
          src={imageUrl || "https://placehold.co/150x150.png"}
          alt="Pakiip Character"
          width={150}
          height={150}
          className="drop-shadow-2xl"
          data-ai-hint="delivery mascot cartoon"
        />
      </div>
    </div>
  );
}
