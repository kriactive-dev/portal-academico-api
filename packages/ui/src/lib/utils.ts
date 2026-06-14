import { clsx, type ClassValue } from "clsx"
import { format } from "date-fns"
import { pt } from "date-fns/locale"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function getIniciais(nome: string) {
  return nome
    .split(" ")
    .filter(Boolean)
    .slice(0, 2)
    .map((parte) => parte[0]?.toUpperCase())
    .join("")
}

export function formatarMoeda(valor: number) {
  return new Intl.NumberFormat("pt-MZ", {
    style: "currency",
    currency: "MZN",
  }).format(valor)
}

export function formatarData(data: string) {
  return format(new Date(data), "dd/MM/yyyy", { locale: pt })
}
