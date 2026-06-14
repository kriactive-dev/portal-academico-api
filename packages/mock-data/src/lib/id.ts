export function gerarId(prefixo: string): string {
  return `${prefixo}-${crypto.randomUUID()}`
}
