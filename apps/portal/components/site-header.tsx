import { GraduationCapIcon } from "lucide-react"

import { ModeToggle } from "@workspace/ui/components/mode-toggle"

export function SiteHeader() {
  return (
    <header className="sticky top-0 z-10 flex h-14 shrink-0 items-center gap-2 border-b bg-background px-4">
      <GraduationCapIcon className="size-5 text-primary" />
      <span className="text-sm font-semibold">Ya Académico</span>
      <div className="ml-auto">
        <ModeToggle />
      </div>
    </header>
  )
}
