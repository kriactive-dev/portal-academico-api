import { Card, CardContent } from "@workspace/ui/components/card"

import { TurmaForm } from "../turma-form"

export default function NovaTurmaPage() {
  return (
    <div className="flex flex-col gap-4">
      <div>
        <h1 className="text-2xl font-semibold">Nova Turma</h1>
        <p className="text-sm text-muted-foreground">
          Criar uma nova turma para um curso.
        </p>
      </div>
      <Card>
        <CardContent>
          <TurmaForm />
        </CardContent>
      </Card>
    </div>
  )
}
