"use client"

import { usePathname } from "next/navigation"

import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbList,
  BreadcrumbPage,
} from "@workspace/ui/components/breadcrumb"
import { ModeToggle } from "@workspace/ui/components/mode-toggle"
import { Separator } from "@workspace/ui/components/separator"
import { SidebarTrigger } from "@workspace/ui/components/sidebar"

const TITULOS: Record<string, string> = {
  "": "Dashboard",
  estudantes: "Estudantes",
  cursos: "Cursos",
  turmas: "Turmas",
  formadores: "Formadores",
  taxas: "Taxas",
  pagamentos: "Pagamentos",
  usuarios: "Usuários & Permissões",
  pedidos: "Pedidos",
}

export function SiteHeader() {
  const pathname = usePathname()
  const segmento = pathname.split("/")[1] ?? ""
  const titulo = TITULOS[segmento] ?? "Ya Académico"

  return (
    <header className="flex h-14 shrink-0 items-center gap-2 border-b px-4">
      <SidebarTrigger className="-ml-1" />
      <Separator orientation="vertical" className="mr-2 h-4" />
      <Breadcrumb>
        <BreadcrumbList>
          <BreadcrumbItem>
            <BreadcrumbPage>{titulo}</BreadcrumbPage>
          </BreadcrumbItem>
        </BreadcrumbList>
      </Breadcrumb>
      <div className="ml-auto">
        <ModeToggle />
      </div>
    </header>
  )
}
