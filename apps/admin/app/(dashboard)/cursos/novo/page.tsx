import { Card, CardContent } from "@workspace/ui/components/card"

import { CursoForm } from "../curso-form"

export default function NovoCursoPage() {
  return (
    <div className="flex flex-col gap-4">
      <div>
        <h1 className="text-2xl font-semibold">Novo Curso</h1>
        <p className="text-sm text-muted-foreground">
          Adicionar um novo curso ao catálogo.
        </p>
      </div>
      <Card>
        <CardContent>
          <CursoForm />
        </CardContent>
      </Card>
    </div>
  )
}
