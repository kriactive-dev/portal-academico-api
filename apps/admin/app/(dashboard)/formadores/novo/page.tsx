import { Card, CardContent } from "@workspace/ui/components/card"

import { FormadorForm } from "../formador-form"

export default function NovoFormadorPage() {
  return (
    <div className="flex flex-col gap-4">
      <div>
        <h1 className="text-2xl font-semibold">Novo Formador</h1>
        <p className="text-sm text-muted-foreground">
          Registar um novo formador no sistema.
        </p>
      </div>
      <Card>
        <CardContent>
          <FormadorForm />
        </CardContent>
      </Card>
    </div>
  )
}
